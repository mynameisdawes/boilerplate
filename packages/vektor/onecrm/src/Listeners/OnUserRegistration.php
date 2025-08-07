<?php

namespace Vektor\OneCRM\Listeners;

use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Vektor\OneCRM\Models\Account;
use Vektor\OneCRM\Models\Contact;

class OnUserRegistration implements ShouldQueue
{
    // use InteractsWithQueue, SerializesModels;
    /**
     * The number of times the queued listener may be attempted.
     *
     * @var int
     */
    public $tries = 2;

    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        if (config('onecrm.enabled')) {
            $user_array = [];
            $user = $event->user;
            if ($user) {
                $user_array = $user->toArray();
            }

            $singular_account_id = config('onecrm.account_id') ? true : false;
            $account_id = config('onecrm.account_id') ? config('onecrm.account_id') : null;
            $contact_id = null;

            $full_name = implode(' ', array_filter([
                $user->first_name,
                $user->last_name,
            ]));

            if ($singular_account_id && !empty($account_id)) {
                $account_response = [
                    'id' => $account_id,
                ];
            } else {
                $_account = new Account();

                $account_data = [
                    'name' => "{$full_name} - {$user->email}",
                    'email1' => $user->email,
                ];

                $_account->fill($account_data);

                $account_response = $_account->persist();
            }

            if ($account_response && isset($account_response['id'])) {
                $_contact = new Contact();

                $_contact->fill([
                    'primary_account_id' => $account_response['id'],
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email1' => $user->email,
                    'email_opt_in' => true,
                ]);

                $contact_response = $_contact->persist();

                if ($contact_response && isset($contact_response['id'])) {
                    $user_configuration = $user->configuration;
                    $user_configuration['onecrm_account_id'] = $account_response['id'];
                    $user_configuration['onecrm_contact_id'] = $contact_response['id'];
                    $user->configuration = $user_configuration;
                    $user->save();
                }
            }
        }
    }
}
