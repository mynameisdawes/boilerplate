<?php

namespace Vektor\Paypal\Http\Controllers\Api;

use Illuminate\Http\Request;
use Vektor\Api\Http\Controllers\ApiController;
use Vektor\Paypal\PaypalPayment;
use Vektor\Shop\Events\CheckoutComplete;
use Vektor\Shop\Events\PaymentSuccess;

class PaypalPaymentController extends ApiController
{
    public function create(Request $request)
    {
        $payment = new PaypalPayment();

        $payment_response = $payment->handle(
            'create',
            $request,
            config('paypal.client_id'),
            config('paypal.client_secret')
        );

        return $this->response($payment_response);
    }

    public function execute(Request $request)
    {
        $payment = new PaypalPayment();

        $payment_response = $payment->handle(
            'execute',
            $request,
            config('paypal.client_id'),
            config('paypal.client_secret')
        );

        if ($this->isSuccess($payment_response)) {
            $request->merge(['payment_method' => 'paypal']);
            PaymentSuccess::dispatch($request);

            $payment_response['data'] = $request->all();

            CheckoutComplete::dispatch($request);
        }

        return $this->response($payment_response);
    }
}
