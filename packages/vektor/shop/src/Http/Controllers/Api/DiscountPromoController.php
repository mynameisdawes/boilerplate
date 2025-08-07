<?php

namespace Vektor\Shop\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Vektor\Api\Http\Controllers\ApiController;
use Vektor\Marketing\Mailchimp;
use Vektor\Shop\Mail\SendDiscountCode;
use Vektor\Shop\Models\Discount;
use Vektor\Shop\Models\DiscountCode;
use Vektor\Shop\Services\HashService;

class DiscountPromoController extends ApiController
{
    public function register(Request $request, $instance = null)
    {
        $discount_id = $request->input('discount_id');
        $email = $request->input('email');

        if ($discount_id && $email) {
            $discount = Discount::where('id', $discount_id)->first();

            if ($discount) {
                $discount_code = HashService::discount_code();

                DiscountCode::create([
                    'discount_id' => $discount->id,
                    'code' => $discount_code,
                ]);

                if (config('marketing.enabled')) {
                    if ($email) {
                        if (config('marketing.mailchimp.enabled') && config('marketing.mailchimp.list_id')) {
                            $mailchimp = new Mailchimp();
                            $mailchimp_client = $mailchimp->boot();

                            $subscriber_email = $email;
                            $subscriber = null;

                            if ($subscriber_email) {
                                try {
                                    $subscriber = $mailchimp_client->lists->getListMember(config('marketing.mailchimp.list_id'), md5($subscriber_email));
                                } catch (\Throwable $th) {
                                }

                                if ($subscriber && ('subscribed' === $subscriber->status || 'archived' === $subscriber->status)) {
                                    return $this->response([
                                        'error' => true,
                                        'error_message' => 'Your email has already been registered',
                                        'http_code' => 403,
                                    ]);
                                }

                                try {
                                    $mailchimp_payload = [
                                        'email_address' => $subscriber_email,
                                        'status_if_new' => 'subscribed',
                                    ];

                                    $mailchimp_response = $mailchimp_client->lists->setListMember(config('marketing.mailchimp.list_id'), md5($subscriber_email), $mailchimp_payload);

                                    Mail::to($email)->send(new SendDiscountCode($discount_code));

                                    return $this->response([
                                        'success' => true,
                                        'success_message' => 'Your code has been sent to your inbox',
                                    ]);
                                } catch (\Throwable $th) {
                                }
                            }
                        }
                    }
                } else {
                    Mail::to($email)->send(new SendDiscountCode($discount_code));

                    return $this->response([
                        'success' => true,
                        'success_message' => 'Your code has been sent to your inbox',
                    ]);
                }
            }
        }

        return $this->response([
            'error' => true,
            'error_message' => 'Your code could not be sent',
            'http_code' => 403,
        ]);
    }
}
