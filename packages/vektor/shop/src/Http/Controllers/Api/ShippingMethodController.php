<?php

namespace Vektor\Shop\Http\Controllers\Api;

use Illuminate\Http\Request;
use Vektor\Api\Http\Controllers\ApiController;
use Vektor\Shop\Services\ShippingCalculatorService;

class ShippingMethodController extends ApiController
{
    public function index(Request $request)
    {
        $shipping_methods = [];
        $product_count = 0;
        $product_subtotal = 0;
        $product_weight = 0;

        $cart_content = \Cart::content();
        if ($cart_content->count() > 0) {
            foreach ($cart_content as $_cart_item) {
                $cart_item = $_cart_item->toArray();
                if ('product' == $cart_item['type']) {
                    $product_count += $cart_item['qty'];
                    $product_subtotal += $cart_item['subtotal'];
                    $product_weight += !empty($cart_item['weight']) ? ($cart_item['weight'] * $cart_item['qty']) : 0;
                }
            }
        }

        $shipping_calculator = new ShippingCalculatorService();
        $shipping_methods = $shipping_calculator->handle($request->input('shipping_country'), $product_count, $product_subtotal, $product_weight);

        return $this->response([
            'success' => true,
            'data' => [
                'shipping_methods' => collect($shipping_methods),
            ],
        ]);
    }
}
