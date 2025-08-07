<?php

namespace Vektor\PasswordlessAuth\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Vektor\PasswordlessAuth\Models\AuthToken;

class RegistrationLink extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $auth_token;

    public function __construct(AuthToken $auth_token)
    {
        $this->auth_token = $auth_token;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Complete your registration',
        );
    }

    public function content(): Content
    {
        $url = route('passwordless.authenticate', $this->auth_token->token);

        return new Content(
            markdown: 'passwordless::emails.registration_link',
            with: [
                'url' => $url,
            ]
        );
    }
}
