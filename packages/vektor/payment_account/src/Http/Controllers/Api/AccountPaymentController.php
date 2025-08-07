<?php

namespace Vektor\Account\Http\Controllers\Api;

use Illuminate\Http\Request;
use Vektor\Account\AccountPayment;
use Vektor\Api\Http\Controllers\ApiController;
use Vektor\Shop\Events\CheckoutComplete;
use Vektor\Shop\Events\PaymentSuccess;

class AccountPaymentController extends ApiController
{
    public function handle(Request $request)
    {
        $payment = new AccountPayment();

        $payment_response = $payment->handle(
            $request
        );

        if ($this->isSuccess($payment_response)) {
            $request->merge(['payment_method' => 'account']);
            PaymentSuccess::dispatch($request);

            $payment_response['data'] = $request->all();

            CheckoutComplete::dispatch($request);
        }

        return $this->response($payment_response);
    }
}
