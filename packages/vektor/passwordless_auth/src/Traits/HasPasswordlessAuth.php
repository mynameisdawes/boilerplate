<?php

namespace Vektor\PasswordlessAuth\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Vektor\PasswordlessAuth\Models\AuthToken;

trait HasPasswordlessAuth
{
    public function authTokens(): HasMany
    {
        return $this->hasMany(AuthToken::class, 'user_id');
    }

    public function sendPasswordlessLoginLink(array $options = []): AuthToken
    {
        return app('passwordless_auth')->sendLoginLink($this, $options);
    }
}
