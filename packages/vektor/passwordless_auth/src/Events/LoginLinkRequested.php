<?php

namespace Vektor\PasswordlessAuth\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Vektor\PasswordlessAuth\Models\AuthToken;

class LoginLinkRequested
{
    use Dispatchable, SerializesModels;

    public $user;
    public $token;

    /**
     * Create a new event instance.
     *
     * @param mixed $user The user model instance
     * @param AuthToken $token The authentication token
     */
    public function __construct($user, AuthToken $token)
    {
        $this->user = $user;
        $this->token = $token;
    }
}
