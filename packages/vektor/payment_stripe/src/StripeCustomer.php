<?php

namespace Vektor\Stripe;

use Illuminate\Http\Request;
use Stripe\Customer;
use Stripe\Exception\InvalidRequestException;
use Stripe\PaymentMethod;
use Stripe\Stripe;

class StripeCustomer
{
    private $secret_key;

    public function __construct($secret_key)
    {
        $this->secret_key = $secret_key;
        Stripe::setApiKey($this->secret_key);
    }

    public function handleCustomer(Request $request)
    {
        $customer = null;

        if ($request->input('payment_method_id')) {
            $params = $this->handleCustomerParams($request);

            if ($request->input('customer_id')) {
                try {
                    $customer = Customer::retrieve($request->input('customer_id'));

                    if (isset($customer->deleted) && $customer->deleted) {
                        $customer = Customer::create($params);
                    } else {
                        if (!empty($params)) {
                            $customer = Customer::update($customer->id, $params);
                        }
                    }
                } catch (InvalidRequestException $e) {
                    $customer = Customer::create($params);
                }
            } else {
                $customer = Customer::create($params);
            }
        }

        return $customer;
    }

    public function getCustomerCards(Request $request)
    {
        $customer_cards = [];

        if ($request->input('customer_id')) {
            try {
                $payment_method_response = PaymentMethod::all([
                    'customer' => $request->input('customer_id'),
                    'type' => 'card',
                    'limit' => 100,
                ]);

                if (isset($payment_method_response->data) && !empty($payment_method_response->data)) {
                    foreach ($payment_method_response->data as $customer_card) {
                        $customer_cards[] = [
                            'id' => $customer_card->id,
                            'brand' => $customer_card->card->brand,
                            'exp_month' => $customer_card->card->exp_month,
                            'exp_year' => $customer_card->card->exp_year,
                            'expiry' => str_pad($customer_card->card->exp_month, 2, '0', STR_PAD_LEFT).'/'.substr($customer_card->card->exp_year, 2),
                            'last4' => $customer_card->card->last4,
                            'number' => '**** **** **** '.$customer_card->card->last4,
                        ];
                    }
                }
            } catch (InvalidRequestException $e) {
                return [
                    'error' => true,
                    'data' => [
                        'customer_cards' => $customer_cards,
                    ],
                ];
            }
        }

        return [
            'success' => true,
            'data' => [
                'customer_cards' => $customer_cards,
            ],
        ];
    }

    public function deleteCustomerCards(Request $request)
    {
        try {
            $payment_method_response = PaymentMethod::retrieve($request->input('id'));
            $payment_method_response->detach();
        } catch (InvalidRequestException $e) {
            return [
                'error' => true,
            ];
        }

        return [
            'success' => true,
        ];
    }

    private function handleCustomerParams(Request $request)
    {
        $params = [];

        $name = implode(' ', array_values(array_filter([
            $request->input('first_name'),
            $request->input('last_name'),
        ])));

        if (!empty($name)) {
            $params['name'] = $name;
        }

        if ($request->input('email')) {
            $params['email'] = $request->input('email');
        }

        if ($request->input('phone')) {
            $params['phone'] = $request->input('phone');
        }

        $address_exists = (count(array_values(array_filter([
            $request->input('billing_address_line_1'),
            $request->input('billing_address_line_2'),
            $request->input('billing_city'),
            $request->input('billing_county'),
            $request->input('billing_postcode'),
            $request->input('billing_country'),
        ]))) > 0) ? true : false;

        if ($address_exists) {
            $params['address'] = [];

            if ($request->input('billing_address_line_1')) {
                $params['address']['line1'] = $request->input('billing_address_line_1');
            }

            if ($request->input('billing_address_line_2')) {
                $params['address']['line2'] = $request->input('billing_address_line_2');
            }

            if ($request->input('billing_city')) {
                $params['address']['city'] = $request->input('billing_city');
            }

            if ($request->input('billing_county')) {
                $params['address']['state'] = $request->input('billing_county');
            }

            if ($request->input('billing_postcode')) {
                $params['address']['postal_code'] = $request->input('billing_postcode');
            }

            if ($request->input('billing_country')) {
                $params['address']['country'] = $request->input('billing_country');
            }
        }

        return $params;
    }
}
