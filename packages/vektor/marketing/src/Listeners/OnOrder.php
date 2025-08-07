<?php

namespace Vektor\Marketing\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Vektor\Marketing\Mailchimp;
use Vektor\Shop\Events\PaymentSuccess;
use Vektor\Utilities\Formatter;

class OnOrder implements ShouldQueue
{
    public $tries = 1;

    protected $user;

    /**
     * Handle the event.
     */
    public function handle(PaymentSuccess $event): void
    {
        $this->request = $event->request;

        $this->formatRequestData();

        if (config('marketing.enabled') && config('shop.agree_marketing')) {
            if (data_get($this->request, 'agree_marketing') && data_get($this->request, 'email')) {
                if (config('marketing.mailchimp.enabled') && config('marketing.mailchimp.list_id')) {
                    $mailchimp = new Mailchimp();
                    $mailchimp_client = $mailchimp->boot();

                    $subscriber_email = data_get($this->request, 'email');
                    if ($subscriber_email) {
                        $mailchimp_payload = [
                            'email_address' => $subscriber_email,
                            'merge_fields' => [
                                'FNAME' => data_get($this->request, 'first_name'),
                                'LNAME' => data_get($this->request, 'last_name'),
                            ],
                            'status_if_new' => 'subscribed',
                        ];

                        $mailchimp_response = $mailchimp_client->lists->setListMember(config('marketing.mailchimp.list_id'), md5($subscriber_email), $mailchimp_payload);
                    }
                }
            }
        }
    }

    private function formatRequestData()
    {
        if (data_get($this->request, 'first_name')) {
            data_set($this->request, 'first_name', Formatter::name(data_get($this->request, 'first_name')));
        }

        if (data_get($this->request, 'last_name')) {
            data_set($this->request, 'last_name', Formatter::name(data_get($this->request, 'last_name')));
        }

        data_set($this->request, 'full_name', implode(' ', array_values(array_filter([
            data_get($this->request, 'first_name'),
            data_get($this->request, 'last_name'),
        ]))));

        if (data_get($this->request, 'email')) {
            data_set($this->request, 'email', Formatter::email(data_get($this->request, 'email')));
        }
    }
}
