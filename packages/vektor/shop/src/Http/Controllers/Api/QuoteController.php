<?php

namespace Vektor\Shop\Http\Controllers\Api;

use Illuminate\Http\Request;
use Vektor\Api\Http\Controllers\ApiController;
use Vektor\OneCRM\Models\Account;
use Vektor\OneCRM\Models\Quote;
use Vektor\Shop\Events\CheckoutComplete;
use Vektor\Shop\Events\PaymentSuccess;
use Vektor\Shop\Models\Product;
use Vektor\Shop\QuoteCheckout;
use Vektor\Utilities\Countries;
use Vektor\Utilities\Formatter;

class QuoteController extends ApiController
{
    public function show(Request $request, $id)
    {
        $_quote = new Quote();
        $quote = $_quote->tally($id);
        $record = $quote['record'];

        $_account = new Account();
        $account = $_account->show($record['billing_account_id'], [
            'email1',
            'phone_office',
        ]);

        if ('Closed Accepted' == $record['quote_stage']) {
            return $this->response([
                'error' => true,
                'error_message' => 'Error: Quote has already been converted',
                'data' => [
                    'already_converted' => true,
                ],
            ]);
        }

        $customisations = isset($record['customisations_data']) ? json_decode($record['customisations_data'], true) : [];

        $date = date_create($record['valid_until']);
        $valid_until = date_format($date, 'M d, Y');

        $quote_data = [
            'can_edit' => '1' == $record['can_edit'] ? true : false,
            'enforce_minimum_quantities' => '1' == $record['enforce_minimum_quantities'] ? true : false,
            'low_res_artwork_provided' => '1' == $record['low_res_artwork_provided'] ? true : false,
            'id' => $record['id'],
            'full_name' => null,
            'first_name' => $record['billing_contact.first_name'],
            'last_name' => $record['billing_contact.last_name'],
            'email' => $account['email1'],
            'phone' => $account['phone_office'],
            'number' => $record['prefix'].$record['quote_number'],
            'name' => $record['name'],
            'status' => $record['quote_stage'],
            'shipping_address_line_1' => null,
            'shipping_address_line_2' => null,
            'shipping_city' => $record['shipping_address_city'],
            'shipping_county' => $record['shipping_address_state'],
            'shipping_postcode' => $record['shipping_address_postalcode'],
            'shipping_country' => null,
            'shipping_country_name' => null,
            'shipping_address_street' => null,
            'shipping_address' => null,
            'billing_address_line_1' => null,
            'billing_address_line_2' => null,
            'billing_city' => $record['billing_address_city'],
            'billing_county' => $record['billing_address_state'],
            'billing_postcode' => $record['billing_address_postalcode'],
            'billing_country' => null,
            'billing_country_name' => null,
            'billing_address_street' => null,
            'billing_address' => null,
            'subtotal' => floatval($record['subtotal']),
            'total_tax' => floatval($record['amount']) - floatval($record['subtotal']),
            'amount' => floatval($record['amount']),
            'formatted' => [
                'subtotal' => Formatter::currency($record['subtotal']),
                'total_tax' => Formatter::currency(floatval($record['amount']) - floatval($record['subtotal'])),
                'amount' => Formatter::currency($record['amount']),
            ],
            'valid_until' => $valid_until,
        ];

        $quote_data['full_name'] = $quote_data['number'].': '.$quote_data['name'];

        if (!empty($record['shipping_address_countrycode'])) {
            $shipping_address_country = Countries::convert($record['shipping_address_countrycode'], 'iso2', 'name');
            if (!empty($shipping_address_country)) {
                $quote_data['shipping_country'] = $record['shipping_address_countrycode'];
                $quote_data['shipping_country_name'] = $shipping_address_country;
            }
        }

        $shipping_address_street = array_map(function ($address_line) {
            return trim($address_line);
        }, explode("\n", $record['shipping_address_street']));

        if (isset($shipping_address_street[0])) {
            $quote_data['shipping_address_line_1'] = $shipping_address_street[0];
        }
        if (isset($shipping_address_street[1])) {
            $quote_data['shipping_address_line_2'] = $shipping_address_street[1];
        }

        $quote_data['shipping_address_street'] = implode('<br />', array_map(function ($address_line) {
            return trim($address_line);
        }, array_values(array_filter([
            $quote_data['shipping_address_line_1'],
            $quote_data['shipping_address_line_2'],
        ]))));

        $quote_data['shipping_address'] = implode('<br />', array_map(function ($address_line) {
            return trim($address_line);
        }, array_values(array_filter([
            $quote_data['shipping_address_line_1'],
            $quote_data['shipping_address_line_2'],
            $quote_data['shipping_city'],
            $quote_data['shipping_county'],
            $quote_data['shipping_postcode'],
            $quote_data['shipping_country_name'],
        ]))));

        if (!empty($record['billing_address_countrycode'])) {
            $billing_address_country = Countries::convert($record['billing_address_countrycode'], 'iso2', 'name');
            if (!empty($billing_address_country)) {
                $quote_data['billing_country'] = $record['billing_address_countrycode'];
                $quote_data['billing_country_name'] = $billing_address_country;
            }
        }

        $billing_address_street = array_map(function ($address_line) {
            return trim($address_line);
        }, explode("\n", $record['billing_address_street']));

        if (isset($billing_address_street[0])) {
            $quote_data['billing_address_line_1'] = $billing_address_street[0];
        }
        if (isset($billing_address_street[1])) {
            $quote_data['billing_address_line_2'] = $billing_address_street[1];
        }

        $quote_data['billing_address_street'] = implode('<br />', array_map(function ($address_line) {
            return trim($address_line);
        }, array_values(array_filter([
            $quote_data['billing_address_line_1'],
            $quote_data['billing_address_line_2'],
        ]))));

        $quote_data['billing_address'] = implode('<br />', array_map(function ($address_line) {
            return trim($address_line);
        }, array_values(array_filter([
            $quote_data['billing_address_line_1'],
            $quote_data['billing_address_line_2'],
            $quote_data['billing_city'],
            $quote_data['billing_county'],
            $quote_data['billing_postcode'],
            $quote_data['billing_country_name'],
        ]))));

        \Cart::instance($record['id']);

        if (
            null == $request->input('return_from')
            || ($request->input('return_from') && 'checkout' !== $request->input('return_from'))
            || ($request->input('return_from') && 'checkout' === $request->input('return_from') && 0 === \Cart::content()->count())
        ) {
            if (!empty($quote['groups']) && 'products' == $quote['groups'][0]['group_type']) {
                \Cart::destroy();

                $items = $quote['groups'][0]['line_items'];

                if ($items && !empty($items)) {
                    foreach ($items as $item) {
                        if (null !== $item['is_comment']) {
                            continue;
                        }

                        $_product = Product::where('sku', $item['mfr_part_no'])->first();

                        if ($_product) {
                            $item_type = 'product';
                            $item['id'] = $_product->id;
                            $item_options = [];

                            if (!empty($_product->attributes)) {
                                foreach ($_product->attributes as $attribute) {
                                    $item_options[$attribute['name']] = $attribute['value'];

                                    break;
                                }
                            }

                            if (!empty($customisations)) {
                                $customisation_count = 1;
                                foreach ($customisations as $customisation_id => $customisation) {
                                    if (!empty($customisation['skus'])) {
                                        foreach ($customisation['skus'] as $sku) {
                                            if (
                                                $sku == $item['mfr_part_no']
                                                && preg_match('/_'.sprintf('%02d', $customisation_count).'$/', $item['name'])
                                            ) {
                                                $item_options['customisation_id'] = $customisation_id;
                                                $item_options['customisations'] = $customisation['designs'];
                                                $item_options['note'] = $customisation['note'];

                                                break 2;
                                            }
                                        }
                                    }

                                    ++$customisation_count;
                                }
                            }

                            if (
                                isset($item_options['customisation_id'], $item_options['customisations'])
                            ) {
                                $customisation_options = [
                                    'designs' => $item_options['customisations'],
                                    'note' => data_get($item_options, 'note'),
                                ];

                                $customisation = \Customisations::add($item_options['customisation_id'], $customisation_options) ?: \Customisations::update($item_options['customisation_id'], $customisation_options);

                                unset($item_options['customisations']);
                            }

                            $item_attributes = data_get($item, 'attributes', []);

                            $cart_item_data = [
                                'type' => $item_type,
                                'id' => data_get($item, 'id'),
                                'qty' => data_get($item, 'quantity', 1),
                                'name' => $_product->name_label,
                                'price' => data_get($item, 'unit_price', 0),
                                'weight' => 0,
                                'options' => $item_options,
                                'attributes' => $item_attributes,
                            ];

                            $cart_item = \Cart::add($cart_item_data)->associate(Product::class);
                        } else {
                            $item_type = 'product';

                            $item_options = [];

                            if (data_get($item, 'mfr_part_no') == config('onecrm.shipping_mfr_part_no')) {
                                $item_type = 'shipping';
                                if (config('onecrm.shipping_custom_provider_id')) {
                                    $item_options['method_name'] = 'Custom';
                                    $item_options['method_code'] = 'custom';
                                    $item_options['shipping_provider_id'] = config('onecrm.shipping_custom_provider_id');
                                }
                            }

                            $item_attributes = data_get($item, 'attributes', []);

                            $cart_item_data = [
                                'type' => $item_type,
                                'id' => data_get($item, 'id'),
                                'qty' => data_get($item, 'quantity', 1),
                                'name' => data_get($item, 'name', ''),
                                'price' => data_get($item, 'unit_price', 0),
                                'weight' => 0,
                                'options' => $item_options,
                                'attributes' => $item_attributes,
                            ];

                            $cart_item = \Cart::add($cart_item_data);
                        }
                    }
                }
            }
        }

        return $this->response([
            'success' => true,
            'data' => [
                'quote' => $quote_data,
            ],
        ]);
    }

    public function handle(Request $request)
    {
        $checkout = new QuoteCheckout();

        $checkout_response = $checkout->handle(
            $request
        );

        if ($this->isSuccess($checkout_response)) {
            $request->merge(['payment_method' => 'quote']);
            PaymentSuccess::dispatch($request);

            $checkout_response['data'] = $request->all();

            CheckoutComplete::dispatch($request);
        }

        return $this->response($checkout_response);
    }
}
