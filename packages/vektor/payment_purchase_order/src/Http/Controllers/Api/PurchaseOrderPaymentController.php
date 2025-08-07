<?php

namespace Vektor\PurchaseOrder\Http\Controllers\Api;

use Illuminate\Http\Request;
use Vektor\Api\Http\Controllers\ApiController;
use Vektor\PurchaseOrder\PurchaseOrderPayment;
use Vektor\Shop\Events\CheckoutComplete;
use Vektor\Shop\Events\PaymentSuccess;

class PurchaseOrderPaymentController extends ApiController
{
    public function handle(Request $request)
    {
        $payment = new PurchaseOrderPayment();

        $payment_response = $payment->handle(
            $request
        );

        if ($this->isSuccess($payment_response)) {
            $request->merge(['payment_method' => 'purchase_order']);
            PaymentSuccess::dispatch($request);

            $payment_response['data'] = $request->all();

            CheckoutComplete::dispatch($request);
        }

        return $this->response($payment_response);
    }
}
