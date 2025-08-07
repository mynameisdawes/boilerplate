<?php

namespace Vektor\Shop\Http\Controllers\Api;

use Illuminate\Http\Request;
use Vektor\Api\Http\Controllers\ApiController;
use Vektor\Shop\Models\Customer;

class CustomerController extends ApiController
{
    public function showByEmail(Request $request)
    {
        $customer = null;

        if (config('shop.customer_unique')) {
            $customer = Customer::where('email', $request->input('email'))->first();
        }

        return $this->response([
            'success' => (null != $customer),
        ]);
    }
}
