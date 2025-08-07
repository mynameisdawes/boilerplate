<?php

namespace App\Listeners;

use App\Mail\QuoteCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Vektor\Shop\Events\QuoteSuccessNotify;

class CustomerNewQuoteEmail implements ShouldQueue
{
    // use InteractsWithQueue, SerializesModels;
    /**
     * The number of times the queued listener may be attempted.
     *
     * @var int
     */
    public $tries = 2;

    /**
     * Handle the event.
     */
    public function handle(QuoteSuccessNotify $event): void
    {
        $request = $event->request;
        $cart = $event->cart;
        $authed_user = $event->authed_user;
        $notification_email = $event->notification_email;
        $quote_id = $event->quote_id;
        $quote_number = $event->quote_number;

        if (!empty($notification_email)) {
            $request->put(
                'shipping_address',
                implode('<br />', array_map(function ($address_line) {
                    return trim($address_line);
                }, array_values(array_filter([
                    data_get($request, 'shipping_address_line_1'),
                    data_get($request, 'shipping_address_line_2'),
                    data_get($request, 'shipping_city'),
                    data_get($request, 'shipping_county'),
                    data_get($request, 'shipping_postcode'),
                ])))),
            );

            $request->put(
                'billing_address',
                implode('<br />', array_map(function ($address_line) {
                    return trim($address_line);
                }, array_values(array_filter([
                    data_get($request, 'billing_address_line_1'),
                    data_get($request, 'billing_address_line_2'),
                    data_get($request, 'billing_city'),
                    data_get($request, 'billing_county'),
                    data_get($request, 'billing_postcode'),
                ])))),
            );

            $product_lines = [];
            if ($cart->product_count > 0) {
                $product_lines = $cart->product_items->map(function ($cart_item) {
                    $cart_item_formatted_attributes = data_get($cart_item, 'formatted.attributes');
                    $cart_item_attributes = [];
                    if (!empty($cart_item_formatted_attributes)) {
                        foreach ($cart_item_formatted_attributes as $attribute) {
                            if (!in_array($attribute['name'], [
                                'size',
                                'colour',
                                'color',
                            ])) {
                                $cart_item_attributes[] = "{$attribute['name_label']}: {$attribute['value_label']}";
                            }
                        }
                    }

                    return [
                        'name' => data_get($cart_item, 'product.name', data_get($cart_item, 'formatted.name')),
                        'attributes' => !empty($cart_item_attributes) ? implode(', ', $cart_item_attributes) : null,
                        'price' => data_get($cart_item, 'formatted.display_price'),
                        'qty' => data_get($cart_item, 'qty'),
                        'subtotal' => data_get($cart_item, 'formatted.display_subtotal'),
                    ];
                });
            }
            $request->put('product_lines', $product_lines);

            $shipping_lines = [];
            if ($cart->shipping_count > 0) {
                $shipping_lines = $cart->shipping_items->map(function ($cart_item) {
                    return [
                        'name' => 'Shipping Method: '.data_get($cart_item, 'formatted.name'),
                        'attributes' => null,
                        'price' => data_get($cart_item, 'formatted.display_price'),
                        'qty' => data_get($cart_item, 'qty'),
                        'subtotal' => data_get($cart_item, 'formatted.display_subtotal'),
                    ];
                });
            }
            $request->put('shipping_lines', $shipping_lines);

            $request->put('admin', false);
            $request->put('notification_email', $notification_email);
            $request->put('quote_id', $quote_id);
            $request->put('quote_number', $quote_number);
            $request->put('product_subtotal', data_get($cart, 'formatted.product_subtotal'));
            $request->put('shipping_subtotal', data_get($cart, 'formatted.shipping_subtotal'));
            $request->put('tax', data_get($cart, 'formatted.tax'));
            $request->put('total', data_get($cart, 'formatted.total'));
            $request->put('low_res_artwork_provided', data_get($request, 'low_res_artwork_provided', false));

            Mail::to($notification_email)->send(new QuoteCreated($request));
        }
    }
}
