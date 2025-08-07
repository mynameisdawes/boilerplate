<?php

namespace Vektor\OneCRM\Listeners;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Vektor\OneCRM\Models\Account;
use Vektor\OneCRM\Models\Contact;
use Vektor\OneCRM\Models\Customisation;
use Vektor\OneCRM\Models\Note;
use Vektor\OneCRM\Models\Quote;
use Vektor\OneCRM\Models\Task;
use Vektor\OneCRM\Models\WrapperInvoice;
use Vektor\OneCRM\Models\WrapperOrder;
use Vektor\OneCRM\Models\WrapperPayment;
use Vektor\OneCRM\Models\WrapperQuote;
use Vektor\OneCRM\Models\WrapperShipping;
use Vektor\OneCRM\OneCRM;
use Vektor\Shop\Events\PaymentSuccess;
use Vektor\Shop\Events\PaymentSuccessNotify;
use Vektor\Shop\Events\QuoteSuccessNotify;
use Vektor\Shop\Models\Customer;
use Vektor\Shop\Models\UserAddress;
use Vektor\Shop\Models\DiscountCode;
use Vektor\Utilities\Countries;
use Vektor\Utilities\Formatter;

class OnOrder implements ShouldQueue
{
    public $tries = 1;

    protected $create_quote = false;
    protected $quote_id;
    protected $quote_number;
    protected $order_id;
    protected $order_number;
    protected $user;
    protected $request = [];
    protected $cart;

    protected $onecrm_account_id_provided = false;
    protected $onecrm_account_id;
    protected $onecrm_contact_id_provided = false;
    protected $onecrm_contact_id;

    protected $shipping_required = false;
    protected $billing_required = false;

    protected $account_response;
    protected $contact_response;
    protected $quote_response;
    protected $order_response;
    protected $shipping_response;
    protected $invoice_response;
    protected $payment_response;
    protected $upload_response;
    protected $note_response;
    protected $task_response;

    /**
     * Handle the event.
     */
    public function handle(PaymentSuccess $event): void
    {
        $this->request = $event->request;

        $this->formatRequestData();

        $event->notification_email = data_get($this->request, 'email');
        $event->create_quote = $this->create_quote;
        $event->quote_id = $this->quote_id;
        $event->quote_number = $this->quote_number;
        $event->order_number = $this->order_number;

        $this->setUser($event);

        if (null == $this->user) {
            return;
        }

        $this->cart = $event->cart;
        $this->shipping_required = config('shop.shipping_required');
        $this->billing_required = config('shop.billing_required');

        $this->saveUserAddresses();

        if (config('onecrm.enabled')) {
            $this->initializeOneCRMConfig();

            if (config('onecrm.on_order.create.accounts')) {
                $this->account_response = $this->handleAccount();
            } else {
                $this->account_response = $this->onecrm_account_id ? ['id' => $this->onecrm_account_id] : null;
            }

            $this->saveUserConfiguration('onecrm_account_id', $this->account_response);

            if ($this->account_response && isset($this->account_response['id'])) {
                if (config('onecrm.on_order.create.contacts')) {
                    $this->contact_response = $this->handleContact();
                } else {
                    $this->contact_response = $this->onecrm_contact_id ? ['id' => $this->onecrm_contact_id] : null;
                }
            }

            $this->saveUserConfiguration('onecrm_contact_id', $this->contact_response);

            if ($this->account_response && isset($this->account_response['id']) && $this->contact_response && isset($this->contact_response['id'])) {
                if ($this->create_quote) {
                    $this->handleQuoteFlow($event);
                } else {
                    $this->handleOrderFlow($event);
                }
            }
        }

        if (config('shop.customer_unique')) {
            Customer::create(['email' => data_get($this->request, 'email')]);
        }

        if (config('shop.new_order_notification')) {
            if ($this->create_quote) {
                QuoteSuccessNotify::dispatch($event);
            } else {
                PaymentSuccessNotify::dispatch($event);
            }
        }
    }

    private function formatRequestData()
    {
        if (data_get($this->request, 'first_name')) {
            data_set($this->request, 'first_name', Formatter::name(data_get($this->request, 'first_name')));
        }

        if (data_get($this->request, 'last_name')) {
            data_set($this->request, 'last_name', Formatter::name(data_get($this->request, 'last_name')));
        }

        data_set($this->request, 'full_name', implode(' ', array_values(array_filter([
            data_get($this->request, 'first_name'),
            data_get($this->request, 'last_name'),
        ]))));

        if (data_get($this->request, 'email')) {
            data_set($this->request, 'email', Formatter::email(data_get($this->request, 'email')));
        }
    }

    private function passwordsProvided()
    {
        return data_get($this->request, 'password') && data_get($this->request, 'password_confirmation') && data_get($this->request, 'password') == data_get($this->request, 'password_confirmation');
    }

    private function createNewUser()
    {
        $user_data = [
            'first_name' => data_get($this->request, 'first_name'),
            'last_name' => data_get($this->request, 'last_name'),
            'email' => data_get($this->request, 'email'),
            'password' => Hash::make(data_get($this->request, 'password')),
        ];

        if (data_get($this->request, 'stripe_customer_id')) {
            $user_data['stripe_id'] = data_get($this->request, 'stripe_customer_id');
        }

        return User::create($user_data);
    }

    private function setUser($event)
    {
        if ($event->authed_user) {
            $this->user = $event->authed_user;
            data_set($this->request, 'email', $this->user->email);
            $this->create_quote = null != $this->user && data_get($this->user, 'configuration.can_create_quotes', false) && 'quote' == data_get($this->request, 'payment_method');
        } else {
            $existing_user = User::where('email', data_get($this->request, 'email'))->first();

            if (!$existing_user && $this->passwordsProvided()) {
                $this->user = $this->createNewUser();
            } else {
                $this->user = $existing_user;
            }
        }
    }

    private function initializeOneCRMConfig()
    {
        $this->onecrm_account_id_provided = config('onecrm.account_id') ? true : false;
        $this->onecrm_account_id = $this->onecrm_account_id_provided ? config('onecrm.account_id') : data_get($this->user, 'configuration.onecrm_account_id');
        $this->onecrm_contact_id_provided = config('onecrm.contact_id') ? true : false;
        $this->onecrm_contact_id = $this->onecrm_contact_id_provided ? config('onecrm.contact_id') : data_get($this->user, 'configuration.onecrm_contact_id');
    }

    private function saveUserAddresses() {
        if (data_get($this->request, "manual_shipping_address") === true && data_get($this->request, "save_shipping_address") === true) {

        }

        if (data_get($this->request, "manual_billing_address") === true && data_get($this->request, "save_billing_address") === true) {

        }
    }

    private function saveUserConfiguration($field, $response)
    {
        if ($response && isset($response['id'])) {
            $user_configuration = $this->user->configuration;
            $user_configuration[$field] = $response['id'];
            $this->user->configuration = $user_configuration;
            $this->user->save();
        }
    }

    private function getAddressData($type, $mapped_type = null)
    {
        if (null == $mapped_type) {
            $mapped_type = $type;
        }

        if ($type == 'shipping' && data_get($this->request, "manual_shipping_address") === false && !empty(data_get($this->request, "shipping_address_id"))) {
            $user_shipping_address = UserAddress::where('id', data_get($this->request, "shipping_address_id"))->first();
            if ($user_shipping_address) {
                data_set($this->request, "{$type}_address_line_1", $user_shipping_address->address_line_1);
                data_set($this->request, "{$type}_address_line_2", $user_shipping_address->address_line_2);
                data_set($this->request, "{$type}_city", $user_shipping_address->city);
                data_set($this->request, "{$type}_county", $user_shipping_address->county);
                data_set($this->request, "{$type}_postcode", $user_shipping_address->postcode);
                data_set($this->request, "{$type}_country", $user_shipping_address->country);
            }
        }

        if ($type == 'billing' && data_get($this->request, "manual_billing_address") === false && !empty(data_get($this->request, "billing_address_id"))) {
            $user_billing_address = UserAddress::where('id', data_get($this->request, "billing_address_id"))->first();
            if ($user_billing_address) {
                data_set($this->request, "{$type}_address_line_1", $user_billing_address->address_line_1);
                data_set($this->request, "{$type}_address_line_2", $user_billing_address->address_line_2);
                data_set($this->request, "{$type}_city", $user_billing_address->city);
                data_set($this->request, "{$type}_county", $user_billing_address->county);
                data_set($this->request, "{$type}_postcode", $user_billing_address->postcode);
                data_set($this->request, "{$type}_country", $user_billing_address->country);
            }
        }

        return [
            "{$mapped_type}_address_street" => array_values(array_filter([
                data_get($this->request, "{$type}_address_line_1"),
                data_get($this->request, "{$type}_address_line_2"),
            ])),
            "{$mapped_type}_address_city" => data_get($this->request, "{$type}_city"),
            "{$mapped_type}_address_state" => data_get($this->request, "{$type}_county"),
            "{$mapped_type}_address_postalcode" => data_get($this->request, "{$type}_postcode"),
            "{$mapped_type}_address_countrycode" => data_get($this->request, "{$type}_country"),
            "{$mapped_type}_address_country" => Countries::convert(data_get($this->request, "{$type}_country"), 'iso2', 'name'),
        ];
    }

    private function mapCartItemsToLines($cart_items, &$parent_data = null)
    {
        return $cart_items->map(function ($cart_item) use (&$parent_data) {
            $line = [
                'id' => $cart_item->id,
                'name' => $cart_item->name,
                'quantity' => $cart_item->qty,
                'unit_price' => $cart_item->price,
                'std_unit_price' => $cart_item->price,
                'ext_price' => $cart_item->subtotal,
                'net_price' => $cart_item->subtotal,
            ];

            $this->appendLineProductAttributes($line, $cart_item);
            $this->appendLineFormattedAttributes($line, $cart_item);
            $this->appendLineCustomisationAttributes($line, $cart_item, $parent_data);
            $this->appendLineShippingAttributes($line, $cart_item, $parent_data);

            return $line;
        })->toArray();
    }

    private function appendLineProductAttributes(&$line, $cart_item)
    {
        if ($cart_item->product) {
            $line['name'] = "{$cart_item->product->name_label} [{$cart_item->product->sku}]";
            if (isset($cart_item->product->configuration)) {
                if (isset($cart_item->product->configuration['onecrm_id'])) {
                    $line['related_type'] = 'ProductCatalog';
                    $line['related_id'] = $cart_item->product->configuration['onecrm_id'];
                    $line['mfr_part_no'] = $cart_item->product->sku;
                }
                if (isset($cart_item->product->configuration['onecrm_tax_code_id'])) {
                    $line['tax_class_id'] = $cart_item->product->configuration['onecrm_tax_code_id'];
                }
                if (isset($cart_item->product->configuration['cost'])) {
                    $line['cost_price'] = $cart_item->product->configuration['cost'];
                }
            }
            if (isset($cart_item->product->attributes) && !empty($cart_item->product->attributes)) {
                foreach ($cart_item->product->attributes as $attribute) {
                    if (isset($attribute['configuration'], $attribute['configuration']['onecrm_id'])) {
                        $line['adjustments'][] = [
                            'id' => $attribute['configuration']['onecrm_id'],
                            'name' => "{$attribute['name_label']} : {$attribute['value_label']}",
                        ];
                    }
                }
            }
        }
    }

    private function appendLineFormattedAttributes(&$line, $cart_item)
    {
        if (isset($cart_item->formatted, $cart_item->formatted->attributes) && !empty($cart_item->formatted->attributes)) {
            $comment_parts = [];
            foreach ($cart_item->formatted->attributes as $attribute) {
                if (!in_array($attribute['name'], ['size', 'colour', 'color'])) {
                    $comment_parts[] = "{$attribute['name_label']}: {$attribute['value_label']}";
                }
            }
            if (!empty($comment_parts)) {
                $line['comment'] = implode(', ', $comment_parts);
            }
        }
    }

    private function appendLineCustomisationAttributes(&$line, $cart_item, &$parent_data = null)
    {
        if ($cart_item->options->get('customisation_id')) {
            $line['customisation_id'] = $cart_item->options->get('customisation_id');
            if ($parent_data && $this->cart->customisations->has($line['customisation_id'])) {
                if (!isset($parent_data['customisations'])) {
                    $parent_data['customisations'] = [];
                }
                if (!isset($parent_data['customisations'][$line['customisation_id']])) {
                    $parent_data['customisations'][$line['customisation_id']] = new Customisation($this->cart->customisations->get($line['customisation_id']), $cart_item->options);
                }
            }
        }
    }

    private function appendLineShippingAttributes(&$line, $cart_item, &$parent_data = null)
    {
        if ('shipping' == $cart_item->type) {
            $line['display_price'] = floatval(Formatter::decimalPlaces($cart_item->subtotal * (1 + (20 / 100))));

            if (config('onecrm.shipping_related_id') && config('onecrm.shipping_mfr_part_no')) {
                $line['related_type'] = 'ProductCatalog';
                $line['related_id'] = config('onecrm.shipping_related_id');
                $line['mfr_part_no'] = config('onecrm.shipping_mfr_part_no');
            }

            if ($cart_item->options->get('method_name')) {
                $line['comment'] = 'Shipping Method: '.$cart_item->options->get('method_name');
            }

            $shipping_provider_id = $cart_item->options->get('shipping_provider_id');

            if ($parent_data && !isset($parent_data['shipping_provider_id']) && !empty($shipping_provider_id)) {
                $parent_data['shipping_provider_id'] = $cart_item->options->get('shipping_provider_id');
            }
        }
    }

    private function generateCartItemDescription($cart_item)
    {
        $description = '';

        if ($cart_item->attributes->get('multi_select')) {
            $cart_item_sizes = array_values(array_filter($cart_item->attributes->get('sizes'), fn ($size) => $size['qty'] > 0));

            if (!empty($cart_item_sizes)) {
                $equals_sm = '====';
                $equals_lg = str_repeat($equals_sm, 2);
                $equals_br = str_repeat($equals_lg, 8);
                $description .= "{$equals_br}\n\n{$cart_item->name}";

                if (!empty($cart_item->formatted->attributes)) {
                    $first_attribute = $cart_item->formatted->attributes[0];
                    $description .= " - {$first_attribute['value_label']}";
                }

                $description .= "\n\n{$equals_lg} QUANTITIES {$equals_lg}\n";

                foreach ($cart_item_sizes as $size) {
                    $second_attribute = $size['formatted']['attributes'][1] ?? null;
                    $description .= "\n{$second_attribute['value_label']}: {$size['qty']}";
                }
            }
        }

        return $description;
    }

    private function updateArtworkStatus($current_status, $customisation)
    {
        $new_status = $customisation->get_artwork_status();

        return null === $current_status ? $new_status : max($current_status, $new_status);
    }

    private function handleQuoteFlow($event)
    {
        $this->quote_response = $this->handleQuote();

        if ($this->quote_response && isset($this->quote_response['quote_number'])) {
            $event->quote_id = $this->quote_response['id'];
            $this->quote_id = $event->quote_id;
            $event->quote_number = "{$this->quote_response['prefix']}{$this->quote_response['quote_number']}";
            $this->quote_number = $event->quote_number;
        }
    }

    private function handleOrderFlow($event)
    {
        if (config('onecrm.on_order.create.sales_orders')) {
            $this->order_response = $this->handleOrder();

            if ($this->order_response && isset($this->order_response['prefix'], $this->order_response['so_number'])) {
                $event->order_id = $this->order_response['id'];
                $this->order_id = $event->order_id;
                $event->order_number = "{$this->order_response['prefix']}{$this->order_response['so_number']}";
                $this->order_number = $event->order_number;
            }
        }

        if ($this->order_response && isset($this->order_response['id'])) {
            $this->handlePostOrderEntities();
        }
    }

    private function handlePostOrderEntities()
    {
        if ($this->shipping_required && config('onecrm.on_order.create.shipping')) {
            $this->shipping_response = $this->handleShipping();
        }

        if ('purchase_order' == data_get($this->request, 'payment_method')) {
            if (Storage::exists('files/'.data_get($this->request, 'purchase_order_file.file_name'))) {
                $one_crm = new OneCRM();

                $file_data = [
                    'headers' => [
                        'Content-Type' => Storage::mimeType('files/'.data_get($this->request, 'purchase_order_file.file_name')),
                        'X-ONECRM-FILENAME' => data_get($this->request, 'purchase_order_file.file_name'),
                    ],
                    'body' => Storage::get('files/'.data_get($this->request, 'purchase_order_file.file_name')),
                ];

                $this->upload_response = $one_crm->post('files/upload', $file_data);

                if (isset($this->upload_response['success']) && true == $this->upload_response['success'] && isset($this->upload_response['data'], $this->upload_response['data']['id'])) {
                    $note = new Note();

                    $note->fill([
                        'name' => 'Purchase Order Note',
                        'description' => 'Purchase Order Number: '.data_get($this->request, 'purchase_order_number')."\nPurchase Order Amount: ".number_format(floatval(data_get($this->request, 'purchase_order_amount')), 2),
                        'filename' => $this->upload_response['data']['id'],
                        'parent_type' => 'SalesOrders',
                        'parent_id' => $this->order_response['id'],
                    ]);

                    $this->note_response = $note->persist();
                }
            }
        } else {
            if (config('onecrm.on_order.create.invoices')) {
                $this->invoice_response = $this->handleInvoice();

                if ($this->invoice_response && isset($this->invoice_response['id']) && config('onecrm.on_order.create.payments')) {
                    $this->payment_response = $this->handlePayment();
                }
            }
        }

        if (config('onecrm.on_order.create.tasks')) {
            $this->task_response = $this->handleTask();
        }
    }

    private function handleAccount()
    {
        $account_data = [
            'name' => data_get($this->request, 'full_name').' - '.data_get($this->request, 'email'),
            'phone_office' => data_get($this->request, 'phone'),
            'email1' => data_get($this->request, 'email'),
        ];

        if ($this->onecrm_account_id) {
            $account_data['id'] = $this->onecrm_account_id;
        }

        $account = new Account();

        if ($this->shipping_required) {
            $account_data = array_merge($account_data, $this->getAddressData('shipping'));
        }

        if ($this->billing_required) {
            $account_data = array_merge($account_data, $this->getAddressData('billing'));
            if ($this->shipping_required && data_get($this->request, 'same_as_shipping')) {
                $account_data = array_merge($account_data, $this->getAddressData('shipping', 'billing'));
            }
        }

        $account->fill($account_data);

        return $account->persist();
    }

    private function handleContact()
    {
        $contact_data = [
            'primary_account_id' => $this->account_response['id'],
            'first_name' => data_get($this->request, 'first_name'),
            'last_name' => data_get($this->request, 'last_name'),
            'phone_work' => data_get($this->request, 'phone'),
            'email1' => data_get($this->request, 'email'),
            'email_opt_in' => false,
        ];

        if ($this->onecrm_contact_id) {
            $contact_data['id'] = $this->onecrm_contact_id;
        }

        $contact = new Contact();

        if ($this->shipping_required) {
            $contact_data = array_merge($contact_data, $this->getAddressData('shipping', 'alt'));
        }

        if ($this->billing_required) {
            $contact_data = array_merge($contact_data, $this->getAddressData('billing', 'primary'));
            if ($this->shipping_required && data_get($this->request, 'same_as_shipping')) {
                $contact_data = array_merge($contact_data, $this->getAddressData('shipping', 'primary'));
            }
        }

        $contact->fill($contact_data);

        return $contact->persist();
    }

    private function handleQuote()
    {
        $_quote = new WrapperQuote();

        if ('collection' == data_get($this->request, 'shipping_type', 'delivery')) {
            $this->shipping_required = false;
            $this->billing_required = true;
            data_set($this->request, 'same_as_shipping', false);
        }

        $onecrm_user_id = null;
        if ($this->user) {
            $onecrm_user_id = data_get($this->user, 'configuration.onecrm_user_id');
        }

        $quote_data = [
            'name' => '['.config('app.company.name').'] - '.data_get($this->request, 'full_name'),
            'amount' => $this->cart->total,
            'subtotal' => $this->cart->subtotal,
            'pretax' => $this->cart->subtotal,
            'billing_account_id' => $this->account_response['id'],
            'billing_contact_id' => $this->contact_response['id'],
            'shipping_account_id' => $this->account_response['id'],
            'shipping_contact_id' => $this->contact_response['id'],
            'description' => data_get($this->request, 'notes'),
            'can_edit' => true == data_get($this->request, 'can_edit', true) ? '1' : '0',
            'enforce_minimum_quantities' => true == data_get($this->request, 'enforce_minimum_quantities', true) ? '1' : '0',
            'low_res_artwork_provided' => true == data_get($this->request, 'low_res_artwork_provided', false) ? '1' : '0',
        ];

        if (!empty($onecrm_user_id)) {
            $quote_data['assigned_user_id'] = $onecrm_user_id;
        }

        $quote_data['lines'] = array_merge(
            $this->mapCartItemsToLines($this->cart->product_items, $quote_data),
            $this->mapCartItemsToLines($this->cart->shipping_items, $quote_data)
        );

        $_quote->fill($quote_data);

        return $_quote->persist();
    }

    private function handleOrder()
    {
        $_order = new WrapperOrder();

        if ('collection' == data_get($this->request, 'shipping_type', 'delivery')) {
            $this->shipping_required = false;
            $this->billing_required = true;
            data_set($this->request, 'same_as_shipping', false);
        }

        $order_data = [
            'name' => '['.config('app.company.name').'] - '.data_get($this->request, 'full_name'),
            'amount' => $this->cart->total,
            'subtotal' => $this->cart->subtotal,
            'pretax' => $this->cart->subtotal,
        ];

        if ($this->shipping_required) {
            $order_data = array_merge($order_data, $this->getAddressData('shipping'));
            $order_data = array_merge($order_data, [
                'shipping_account_id' => $this->account_response['id'],
                'shipping_contact_id' => $this->contact_response['id'],
            ]);
        }

        if ($this->billing_required) {
            $order_data = array_merge($order_data, $this->getAddressData('billing'));
            $order_data = array_merge($order_data, [
                'billing_account_id' => $this->account_response['id'],
                'billing_contact_id' => $this->contact_response['id'],
            ]);
            if ($this->shipping_required && data_get($this->request, 'same_as_shipping')) {
                $order_data = array_merge($order_data, $this->getAddressData('shipping', 'billing'));
            }
        }

        if (data_get($this->request, 'from_model') && data_get($this->request, 'from_id')) {
            if ('quote' == data_get($this->request, 'from_model')) {
                return $this->handleQuoteOrder($order_data);
            }
        } else {
            $order_data['lines'] = $this->mapCartItemsToLines($this->cart->product_items, $order_data);

            $discount_code = null;
            if (floatval($this->cart->discount) > 0) {
                $discount_code = $this->cart->discount_code;
                $discount_code_model = DiscountCode::where('code', $discount_code)->first();

                if ($discount_code_model) {
                    $discount_line = [
                        'id' => 'discount',
                        'name' => "Discount: [{$discount_code_model->id}:{$discount_code}]",
                        'quantity' => 1,
                        'unit_price' => floatval($this->cart->discount) * -1,
                        'std_unit_price' => floatval($this->cart->discount) * -1,
                        'ext_price' => floatval($this->cart->discount) * -1,
                        'net_price' => floatval($this->cart->discount) * -1,
                    ];

                    $order_data['lines'][] = $discount_line;
                }
            }

            $order_data['lines'] = array_merge(
                $order_data['lines'],
                $this->mapCartItemsToLines($this->cart->shipping_items, $order_data)
            );

            $_order->fill($order_data);

            return $_order->persist();
        }
    }

    private function handleQuoteOrder($order_data)
    {
        $order_data['lines'] = [];
        if ($this->cart->product_count > 0) {
            $order_data['lines'] = array_merge($order_data['lines'], $this->mapCartItemsToLines($this->cart->product_items, $order_data));
        }

        $discount_code = null;
        if (floatval($this->cart->discount) > 0) {
            $discount_code = $this->cart->discount_code;
            $discount_code_model = DiscountCode::where('code', $discount_code)->first();

            if ($discount_code_model) {
                $discount_line = [
                    'id' => 'discount',
                    'name' => "Discount: [{$discount_code_model->id}:{$discount_code}]",
                    'quantity' => 1,
                    'unit_price' => floatval($this->cart->discount) * -1,
                    'std_unit_price' => floatval($this->cart->discount) * -1,
                    'ext_price' => floatval($this->cart->discount) * -1,
                    'net_price' => floatval($this->cart->discount) * -1,
                ];

                $order_data['lines'][] = $discount_line;
            }
        }

        if ($this->cart->shipping_count > 0) {
            $order_data['lines'] = array_merge($order_data['lines'], $this->mapCartItemsToLines($this->cart->shipping_items, $order_data));
        }

        $_quote = new Quote();
        $this->quote_response = $_quote->tally(data_get($this->request, 'from_id'));

        $order_data['related_quote_id'] = data_get($this->request, 'from_id');

        $_order = new WrapperOrder();

        $_order->fill($order_data);

        return $_order->persistFromModel($this->quote_response, 'related_quote_id', data_get($this->request, 'from_id'));
    }

    private function handleShipping()
    {
        if ($this->cart->shipping_count > 0) {
            $shippingItem = $this->cart->shipping_items->first();

            $_shipping = new WrapperShipping();

            if ('collection' == data_get($this->request, 'shipping_type', 'delivery')) {
                $this->shipping_required = false;
            }

            $shipping_data = [
                'so_id' => $this->order_response['id'],
                'name' => '['.config('app.company.name').'] - '.data_get($this->request, 'full_name'),
                'shipping_cost' => $shippingItem->price,
                'shipping_account_id' => $this->account_response['id'],
                'shipping_contact_id' => $this->contact_response['id'],
            ];

            if ($this->shipping_required) {
                $shipping_data = array_merge($shipping_data, $this->getAddressData('shipping'));
            }

            $shipping_data['lines'] = $this->mapCartItemsToLines($this->cart->product_items, $shipping_data);
            $this->mapCartItemsToLines($this->cart->shipping_items, $shipping_data);

            $_shipping->fill($shipping_data);

            return $_shipping->persist();
        }

        return null;
    }

    private function handleInvoice()
    {
        $_invoice = new WrapperInvoice();

        $invoice_data = [
            'from_so_id' => $this->order_response['id'],
            'name' => '['.config('app.company.name').'] - '.data_get($this->request, 'full_name'),
            'amount' => $this->cart->total,
            'subtotal' => $this->cart->subtotal,
            'pretax' => $this->cart->subtotal,
        ];

        if ($this->shipping_required) {
            $invoice_data = array_merge($invoice_data, $this->getAddressData('shipping'));
            $invoice_data = array_merge($invoice_data, [
                'shipping_account_id' => $this->account_response['id'],
                'shipping_contact_id' => $this->contact_response['id'],
            ]);
        }

        if ($this->billing_required) {
            $invoice_data = array_merge($invoice_data, $this->getAddressData('billing'));
            $invoice_data = array_merge($invoice_data, [
                'billing_account_id' => $this->account_response['id'],
                'billing_contact_id' => $this->contact_response['id'],
            ]);
            if ($this->shipping_required && data_get($this->request, 'same_as_shipping')) {
                $invoice_data = array_merge($invoice_data, $this->getAddressData('shipping', 'billing'));
            }
        }

        $invoice_data['lines'] = $this->mapCartItemsToLines($this->cart->product_items, $invoice_data);

        $discount_code = null;
        if (floatval($this->cart->discount) > 0) {
            $discount_code = $this->cart->discount_code;
            $discount_code_model = DiscountCode::where('code', $discount_code)->first();

            if ($discount_code_model) {
                $discount_line = [
                    'id' => 'discount',
                    'name' => "Discount: [{$discount_code_model->id}:{$discount_code}]",
                    'quantity' => 1,
                    'unit_price' => floatval($this->cart->discount) * -1,
                    'std_unit_price' => floatval($this->cart->discount) * -1,
                    'ext_price' => floatval($this->cart->discount) * -1,
                    'net_price' => floatval($this->cart->discount) * -1,
                ];

                $invoice_data['lines'][] = $discount_line;
            }
        }

        $invoice_data['lines'] = array_merge(
            $invoice_data['lines'],
            $this->mapCartItemsToLines($this->cart->shipping_items, $invoice_data)
        );

        $_invoice->fill($invoice_data);

        return $_invoice->persist();
    }

    private function handlePayment()
    {
        $_payment = new WrapperPayment();

        $payment_data = [
            'related_invoice_id' => $this->invoice_response['id'],
            'account_id' => $this->account_response['id'],
            'amount' => $this->cart->total,
            'total_amount' => $this->cart->total,
            'customer_reference' => data_get($this->request, 'payment_intent_id', data_get($this->request, 'full_name')),
        ];

        $_payment->fill($payment_data);

        return $_payment->persist();
    }

    private function handleTask()
    {
        $_task = new Task();

        $task_data = [
            'name' => '['.config('app.company.name').']'.(!empty($this->order_number) ? " - {$this->order_number}" : ' - '.data_get($this->request, 'full_name')),
            'parent_id' => $this->order_response['id'],
            'account_id' => $this->account_response['id'],
        ];

        $description = '';
        $artwork_status = null;

        if ($this->cart->product_count > 0) {
            $this->cart->grouped->product_items->each(function ($cart_item) use (&$description, &$artwork_status) {
                if ($cart_item->options->get('customisation_id') && $this->cart->customisations->has($cart_item->options->get('customisation_id'))) {
                    $cart_item_description = $this->generateCartItemDescription($cart_item);
                    $description .= $cart_item_description;

                    $cart_item->options->put('so_id', $this->order_response['id']);

                    $cart_item_customisation = new Customisation($this->cart->customisations->get($cart_item->options->get('customisation_id')), $cart_item->options);

                    $description .= "\n".$cart_item_customisation->get_comment()."\n\n";

                    $artwork_status = $this->updateArtworkStatus($artwork_status, $cart_item_customisation);
                }
            });

            $description = trim($description);
        }

        if (!empty($description)) {
            $task_data['description'] = $description;
        }

        if (null !== $artwork_status) {
            $task_data['artwork_status'] = $artwork_status;
        }

        $_task->fill($task_data);

        return $_task->persist();
    }
}
