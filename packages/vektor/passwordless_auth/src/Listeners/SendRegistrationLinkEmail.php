<?php

namespace Vektor\PasswordlessAuth\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Vektor\PasswordlessAuth\Events\RegistrationLinkRequested;
use Vektor\PasswordlessAuth\Mail\RegistrationLink;

class SendRegistrationLinkEmail implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param RegistrationLinkRequested $event
     * @return void
     */
    public function handle(RegistrationLinkRequested $event)
    {
        // Check if email sending is enabled in config
        if (!config('passwordless_auth.send_emails', true)) {
            return;
        }

        Mail::to($event->email)->send(new RegistrationLink($event->token));
    }
}
