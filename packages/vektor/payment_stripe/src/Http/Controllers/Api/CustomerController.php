<?php

namespace Vektor\Stripe\Http\Controllers\Api;

use Illuminate\Http\Request;
use Vektor\Api\Http\Controllers\ApiController;
use Vektor\Stripe\StripeCustomer;

class CustomerController extends ApiController
{
    public function getCustomerCards(Request $request)
    {
        $customer = new StripeCustomer(config('stripe.secret_key'));

        $response = $customer->getCustomerCards($request);

        return $this->response($response);
    }

    public function deleteCustomerCards(Request $request)
    {
        $customer = new StripeCustomer(config('stripe.secret_key'));

        $response = $customer->deleteCustomerCards($request);

        return $this->response($response);
    }
}
