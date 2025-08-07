<?php

namespace Vektor\PasswordlessAuth\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Vektor\PasswordlessAuth\Models\AuthToken;

class RegistrationLinkRequested
{
    use Dispatchable, SerializesModels;

    public $email;
    public $token;
    public $user_data;

    /**
     * Create a new event instance.
     *
     * @param string $email The email address
     * @param AuthToken $token The authentication token
     * @param array $user_data Additional user data
     */
    public function __construct(string $email, AuthToken $token, array $user_data = [])
    {
        $this->email = $email;
        $this->token = $token;
        $this->user_data = $user_data;
    }
}
