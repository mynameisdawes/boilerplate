<?php

namespace Vektor\Stripe;

use Illuminate\Http\Request;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\CardException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\RateLimitException;
use Stripe\SetupIntent;
use Stripe\Stripe;

class StripeSetupIntent
{
    private $secret_key;

    public function __construct($secret_key)
    {
        $this->secret_key = $secret_key;
        Stripe::setApiKey($this->secret_key);
    }

    public function handleSetupIntent(Request $request)
    {
        try {
            if ($request->input('payment_method_id')) {
                $intent = SetupIntent::create([
                    'customer' => $request->input('customer_id'),
                    'payment_method_types' => ['card'],
                    'payment_method' => $request->input('payment_method_id'),
                    'usage' => 'off_session',
                    'confirm' => true,
                ]);
            }

            if ($request->input('setup_intent_id')) {
                $intent = SetupIntent::retrieve($request->input('setup_intent_id'));
            }

            if ($intent && in_array($intent->status, ['requires_action', 'requires_source_action']) && 'use_stripe_sdk' == $intent->next_action->type) {
                return [
                    'success' => true,
                    'data' => [
                        'requires_action' => true,
                        'intent_client_secret' => $intent->client_secret,
                        'customer' => $request->input('customer_id'),
                        'payment_method' => $request->input('payment_method_id'),
                    ],
                ];
            }
            if ($intent && 'succeeded' == $intent->status) {
                return [
                    'success' => true,
                    'success_message' => $request->input('success_message', 'Your card was added successfully'),
                    'data' => [
                        'status' => 'succeeded',
                        'customer' => $request->input('customer_id'),
                        'payment_method' => $intent->payment_method,
                        'setup_intent_id' => $intent->id,
                    ],
                ];
            }

            return [
                'error' => true,
                'error_message' => 'Invalid status',
                'http_code' => 500,
            ];
        } catch (CardException $e) {
            // The card has been declined
            $error_code = $e->getHttpStatus();
            if (3 != strlen($error_code) || !is_numeric($error_code)) {
                $error_code = 403;
            }

            return [
                'error' => true,
                'error_message' => $e->getError()->message,
                'http_code' => $error_code,
                'data' => [
                    'error_type' => $e->getError()->type,
                    'error_code' => $e->getError()->code,
                ],
            ];
        } catch (RateLimitException $e) {
            // The API request has been throttled
            return [
                'error' => true,
                'error_message' => $e->getMessage(),
                'http_code' => 429,
            ];
        } catch (InvalidRequestException $e) {
            // Invalid parameters were supplied to the API
            return [
                'error' => true,
                'error_message' => $e->getMessage(),
                'http_code' => 400,
            ];
        } catch (AuthenticationException $e) {
            // Authentication with the API has failed
            return [
                'error' => true,
                'error_message' => $e->getMessage(),
                'http_code' => 401,
            ];
        } catch (ApiConnectionException $e) {
            // Network communication with the API has failed
            return [
                'error' => true,
                'error_message' => $e->getMessage(),
                'http_code' => 402,
            ];
        } catch (ApiErrorException $e) {
            // Generic failure related to the API
            return [
                'error' => true,
                'error_message' => $e->getMessage(),
                'http_code' => 500,
            ];
        } catch (\Exception $e) {
            // Generic failure unrelated to the API
            return [
                'error' => true,
                'error_message' => $e->getMessage(),
                'http_code' => 500,
            ];
        }
    }

    public function setupIntent(Request $request)
    {
        $stripe_customer = new StripeCustomer($this->secret_key);
        $customer = $stripe_customer->handleCustomer($request);
        if ($customer && isset($customer->id)) {
            $request->merge([
                'customer_id' => $customer->id,
            ]);
        }

        return $this->handleSetupIntent($request);
    }
}
