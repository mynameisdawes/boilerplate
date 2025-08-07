<?php

namespace Vektor\Shop\Http\Controllers;

use App\Mail\QuoteReminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Vektor\Api\Http\Controllers\ApiController;
use Vektor\OneCRM\Models\Contact;
use Vektor\OneCRM\Models\Quote;

class QuoteController extends ApiController
{
    public function preview(Request $request, $quote_id)
    {
        return view('shop::quote', ['id' => $quote_id]);
    }

    public function checkout(Request $request, $quote_id)
    {
        $user = \Auth::user();

        $stripe_customer_id = null;
        if ($user) {
            $stripe_customer_id = data_get($user, 'configuration.stripe_customer_id');
        }

        return view('shop::checkout', ['stripe_customer_id' => $stripe_customer_id, 'instance' => $quote_id, 'model' => 'quote']);
    }

    public function email(Request $request)
    {
        $workflow_data = $request->all();

        if (
            isset($workflow_data['module'])
            && 'Quotes' == $workflow_data['module']
            && isset($workflow_data['record'])
        ) {
            $quote = [
                'quote_id' => null,
                'data' => [
                    'first_name' => null,
                ],
            ];
            $_quote = new Quote();

            $quote_response = $_quote->show($workflow_data['record']);

            if ($quote_response && isset($quote_response['id'])) {
                $quote['quote_id'] = $quote_response['id'];
                if (!empty($quote_response['shipping_contact_id'])) {
                    $_contact = new Contact();
                    $contact_response = $_contact->show($quote_response['shipping_contact_id']);
                    if ($contact_response && isset($contact_response['id'])) {
                        $quote['data']['first_name'] = $contact_response['first_name'];
                        if (!empty($contact_response['email1'])) {
                            Mail::to($contact_response['email1'])->send(new QuoteReminder($quote));
                        }
                    }
                }
            }
        }
    }
}
