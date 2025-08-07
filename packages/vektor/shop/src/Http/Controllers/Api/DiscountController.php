<?php

namespace Vektor\Shop\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Vektor\Api\Http\Controllers\ApiController;
use Vektor\Shop\Models\DiscountCode;
use Vektor\Shop\Utilities as ShopUtilities;

class DiscountController extends ApiController
{
    public function apply(Request $request, $instance = null)
    {
        $options = [];

        if ($instance) {
            \Cart::instance($instance);
            $options['instance'] = $instance;
        }

        $code = $request->input('discount_code');

        if ($code) {
            $discount_code = DiscountCode::with(['discount'])->where('code', $code)->first();

            if ($discount_code && $discount_code->discount) {
                if ($discount_code->is_used) {
                    $request->session()->forget('cart_discount_code.'.\Cart::currentInstance());
                    \Cart::setGlobalDiscount(0);

                    return $this->response([
                        'error' => true,
                        'error_message' => 'The discount code you have provided has already been used',
                        'http_code' => 403,
                    ]);
                }

                $now = Carbon::now();
                $discount_code_in_valid_date_range = (null === $discount_code->discount->start_date || $discount_code->discount->start_date <= $now) && (null === $discount_code->discount->end_date || $discount_code->discount->end_date >= $now);

                if (false === $discount_code_in_valid_date_range) {
                    $request->session()->forget('cart_discount_code.'.\Cart::currentInstance());
                    \Cart::setGlobalDiscount(0);

                    return $this->response([
                        'error' => true,
                        'error_message' => 'The discount code you have provided has expired',
                        'http_code' => 403,
                    ]);
                }

                if (in_array($discount_code->discount->type, ['percentage', 'fixed'])) {
                    if ('percentage' == $discount_code->discount->type) {
                        $request->session()->put('cart_discount_code.'.\Cart::currentInstance(), $discount_code->code);
                        \Cart::setGlobalDiscount($discount_code->discount->amount);
                    }

                    $cart = ShopUtilities::cart($options);

                    return $this->response([
                        'success' => true,
                        'success_message' => 'The discount code has been applied successfully',
                        'data' => $cart,
                    ]);
                }
            }
        }

        $request->session()->forget('cart_discount_code.'.\Cart::currentInstance());
        \Cart::setGlobalDiscount(0);

        return $this->response([
            'error' => true,
            'error_message' => 'The discount code you have provided is not valid',
            'http_code' => 404,
        ]);
    }

    public function cancel(Request $request, $instance = null)
    {
        $options = [];

        if ($instance) {
            \Cart::instance($instance);
            $options['instance'] = $instance;
        }

        $request->session()->forget('cart_discount_code.'.\Cart::currentInstance());
        \Cart::setGlobalDiscount(0);

        $cart = ShopUtilities::cart($options);

        return $this->response([
            'success' => true,
            'success_message' => 'The discount code has been removed successfully',
            'data' => $cart,
        ]);
    }
}
