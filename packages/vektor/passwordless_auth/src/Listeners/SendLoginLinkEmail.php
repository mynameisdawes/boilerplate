<?php

namespace Vektor\PasswordlessAuth\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Vektor\PasswordlessAuth\Events\LoginLinkRequested;
use Vektor\PasswordlessAuth\Mail\LoginLink;

class SendLoginLinkEmail implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param LoginLinkRequested $event
     * @return void
     */
    public function handle(LoginLinkRequested $event)
    {
        // Check if email sending is enabled in config
        if (!config('passwordless_auth.send_emails', true)) {
            return;
        }

        Mail::to($event->user)->send(new LoginLink($event->token));
    }
}
