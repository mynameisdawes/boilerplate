<?php

namespace Vektor\PasswordlessAuth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthToken extends Model
{
    public const TYPE_LOGIN = 'login';
    public const TYPE_REGISTRATION = 'registration';

    protected $fillable = [
        'user_id',
        'email',
        'token',
        'type',
        'metadata',
        'expires_at',
        'expires_at_expiry',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'expires_at_expiry' => 'boolean',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('passwordless_auth.user_model', 'App\Models\User'));
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isRegistration(): bool
    {
        return self::TYPE_REGISTRATION === $this->type;
    }

    public function isLogin(): bool
    {
        return self::TYPE_LOGIN === $this->type;
    }
}
