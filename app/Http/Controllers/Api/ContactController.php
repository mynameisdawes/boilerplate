<?php

namespace App\Http\Controllers\Api;

use App\Mail\ContactFormSubmitted;
use Illuminate\Http\Request;
use Vektor\Api\Http\Controllers\ApiController;
use Vektor\Marketing\Mailchimp;
use Vektor\Utilities\Formatter;

class ContactController extends ApiController
{
    public function handle(Request $request)
    {
        $data = [
            'title' => $request->input('title'),
            'first_name' => Formatter::name($request->input('first_name')),
            'last_name' => Formatter::name($request->input('last_name')),
            'email' => Formatter::email($request->input('email')),
            'phone' => Formatter::phone($request->input('phone')),
            'agree_marketing' => 'true' == $request->input('agree_marketing') ? true : false,
            'light' => 'true' == $request->input('light') ? true : false,
            'callback' => $request->input('callback'),
            'address_line_1' => Formatter::name($request->input('address_line_1')),
            'address_line_2' => Formatter::name($request->input('address_line_2')),
            'city' => Formatter::name($request->input('city')),
            'county' => Formatter::name($request->input('county')),
            'postcode' => Formatter::postcode($request->input('postcode')),
            'country' => $request->input('country'),
            'message' => $request->input('message'),
        ];

        if (config('marketing.enabled')) {
            if (data_get($data, 'agree_marketing') && data_get($data, 'email')) {
                if (config('marketing.mailchimp.enabled') && config('marketing.mailchimp.list_id')) {
                    $mailchimp = new Mailchimp();
                    $mailchimp_client = $mailchimp->boot();

                    $subscriber_email = data_get($data, 'email');
                    if ($subscriber_email) {
                        $mailchimp_payload = [
                            'email_address' => $subscriber_email,
                            'merge_fields' => [
                                'FNAME' => data_get($data, 'first_name'),
                                'LNAME' => data_get($data, 'last_name'),
                            ],
                            'status_if_new' => 'subscribed',
                        ];

                        $mailchimp_response = $mailchimp_client->lists->setListMember(config('marketing.mailchimp.list_id'), md5($subscriber_email), $mailchimp_payload);
                    }
                }
            }
        }

        \Mail::to(config('app.company.email'))->send(new ContactFormSubmitted($data));

        return $this->response([
            'success' => true,
            'success_message' => 'The form submitted successfully',
            // 'data' => $request->all()
        ]);
    }
}
