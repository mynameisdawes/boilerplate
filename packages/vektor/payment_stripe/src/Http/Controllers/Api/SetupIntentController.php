<?php

namespace Vektor\Stripe\Http\Controllers\Api;

use Illuminate\Http\Request;
use Vektor\Api\Http\Controllers\ApiController;
use Vektor\Shop\Events\CheckoutComplete;
use Vektor\Shop\Events\PaymentSuccess;
use Vektor\Stripe\StripeSetupIntent;

class SetupIntentController extends ApiController
{
    public function setupIntent(Request $request)
    {
        $payment = new StripeSetupIntent(config('stripe.secret_key'));

        $payment_response = $payment->setupIntent($request);

        if ($this->isSuccess($payment_response) && isset($payment_response['data'], $payment_response['data']['status']) && 'succeeded' == $payment_response['data']['status']) {
            if (isset($payment_response['data']['customer']) && !empty($payment_response['data']['customer'])) {
                $request->merge(['stripe_customer_id' => $payment_response['data']['customer']]);
                $this->storeStripeCustomerId($request);
            }

            if (isset($payment_response['data']['setup_intent_id']) && !empty($payment_response['data']['setup_intent_id'])) {
                $request->merge(['setup_intent_id' => $payment_response['data']['setup_intent_id']]);
            }

            $request->merge(['payment_method' => 'stripe']);
            PaymentSuccess::dispatch($request);

            $payment_response['data'] = $request->all();

            CheckoutComplete::dispatch($request);
        }

        return $this->response($payment_response);
    }

    public function storeStripeCustomerId(Request $request)
    {
        $user = \Auth::user();

        if ($user && $request->input('stripe_customer_id')) {
            $user->stripe_id = $request->input('stripe_customer_id');
            $user->save();

            return $this->response([
                'success' => true,
            ]);
        }

        return $this->response([
            'error' => true,
            'http_code' => 404,
        ]);
    }
}
