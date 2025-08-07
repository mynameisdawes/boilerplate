<?php

namespace Vektor\Cash\Http\Controllers\Api;

use Illuminate\Http\Request;
use Vektor\Api\Http\Controllers\ApiController;
use Vektor\Cash\CashPayment;
use Vektor\Shop\Events\CheckoutComplete;
use Vektor\Shop\Events\PaymentSuccess;

class CashPaymentController extends ApiController
{
    public function handle(Request $request)
    {
        $payment = new CashPayment();

        $payment_response = $payment->handle(
            $request
        );

        if ($this->isSuccess($payment_response)) {
            $request->merge(['payment_method' => 'cash']);
            PaymentSuccess::dispatch($request);

            $payment_response['data'] = $request->all();

            CheckoutComplete::dispatch($request);
        }

        return $this->response($payment_response);
    }
}
