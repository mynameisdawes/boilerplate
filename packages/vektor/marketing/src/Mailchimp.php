<?php

namespace Vektor\Marketing;

use MailchimpMarketing\ApiClient;

class Mailchimp
{
    public function __construct()
    {
        if (config('marketing.mailchimp.enabled')) {
            if (
                empty(config('marketing.mailchimp.api_key'))
                || empty(config('marketing.mailchimp.server'))
            ) {
                throw new \Exception('Mailchimp config credentials are missing');
            }
        }
    }

    public function boot()
    {
        if (config('marketing.mailchimp.enabled')) {
            $mailchimp = new ApiClient();

            $mailchimp->setConfig([
                'apiKey' => config('marketing.mailchimp.api_key'),
                'server' => config('marketing.mailchimp.server'),
            ]);

            return $mailchimp;
        }

        throw new \Exception("Mailchimp hasn't been enabled");
    }
}
