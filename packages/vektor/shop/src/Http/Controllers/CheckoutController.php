<?php

namespace Vektor\Shop\Http\Controllers;

use Illuminate\Http\Request;
use Vektor\Api\Http\Controllers\ApiController;

class CheckoutController extends ApiController
{
    public function index(Request $request)
    {
        if (0 == \Cart::count()) {
            return redirect()->route('shop.cart.index');
        }

        $user = \Auth::user();
        $stripe_customer_id = null;
        $first_name = null;
        $last_name = null;
        $email = null;

        if ($user) {
            $stripe_customer_id = data_get($user, 'stripe_id');
            $first_name = data_get($user, 'first_name');
            $last_name = data_get($user, 'last_name');
            $email = data_get($user, 'email');
        }

        $view_variables = [
            'stripe_customer_id' => $stripe_customer_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
        ];

        return view('shop::checkout', $view_variables);
    }
}
