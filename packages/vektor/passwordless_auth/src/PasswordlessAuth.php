<?php

namespace Vektor\PasswordlessAuth;

use Illuminate\Support\Str;
use Vektor\PasswordlessAuth\Events\LoginLinkRequested;
use Vektor\PasswordlessAuth\Events\RegistrationLinkRequested;
use Vektor\PasswordlessAuth\Models\AuthToken;

class PasswordlessAuth
{
    public function sendAdminLoginLink($user, array $options = [])
    {
        // Delete old tokens
        $user->authTokens()->where('type', AuthToken::TYPE_LOGIN)->delete();

        // Create new token
        $token = $user->authTokens()->create([
            'token' => Str::random(64),
            'type' => AuthToken::TYPE_LOGIN,
            'expires_at' => now()->addDays(30),
            'ip_address' => $options['ip_address'] ?? null,
            'user_agent' => $options['user_agent'] ?? null,
        ]);

        return $token;
    }

    public function sendLoginLink($user, array $options = [])
    {
        // Delete old tokens
        $user->authTokens()->where('type', AuthToken::TYPE_LOGIN)->where(function ($query) {
            $query->where('expires_at_expiry', 0)->orWhere(function ($query) {
                $query->where('expires_at_expiry', 1)->where('expires_at', '<', now());
            });
        })->delete();

        // Create new token
        $token = $user->authTokens()->create([
            'token' => Str::random(64),
            'type' => AuthToken::TYPE_LOGIN,
            'expires_at' => now()->addMinutes(config('passwordless_auth.token_lifetime', 15)),
            'ip_address' => $options['ip_address'] ?? null,
            'user_agent' => $options['user_agent'] ?? null,
        ]);

        // Dispatch event for email sending
        event(new LoginLinkRequested($user, $token));

        return $token;
    }

    public function sendRegistrationLink($email, array $user_data = [], array $options = [])
    {
        // Delete old registration tokens for this email
        AuthToken::where('email', $email)->where('type', AuthToken::TYPE_REGISTRATION)->delete();

        // Create new token
        $token = AuthToken::create([
            'email' => $email,
            'token' => Str::random(64),
            'type' => AuthToken::TYPE_REGISTRATION,
            'metadata' => $user_data,
            'expires_at' => now()->addMinutes(config('passwordless_auth.token_lifetime', 15)),
            'ip_address' => $options['ip_address'] ?? null,
            'user_agent' => $options['user_agent'] ?? null,
        ]);

        // Dispatch event for email sending
        event(new RegistrationLinkRequested($email, $token, $user_data));

        return $token;
    }

    public function verifyToken($tokenString, array $options = [])
    {
        $token = AuthToken::where('token', $tokenString)->first();

        if (!$token || $token->isExpired()) {
            return ['success' => false, 'error' => 'invalid_token'];
        }

        if (!$this->validateTokenSecurity($token, $options)) {
            return ['success' => false, 'error' => 'security_validation_failed'];
        }

        if ($token->isRegistration()) {
            $user = $this->createUserFromToken($token);
            if (!$user) {
                return ['success' => false, 'error' => 'user_creation_failed'];
            }

            $token->delete();

            return [
                'success' => true,
                'user' => $user,
                'type' => 'registration',
                'created' => true,
            ];
        }

        return [
            'success' => true,
            'user' => $token->user,
            'type' => 'login',
            'created' => false,
        ];
    }

    public function deleteToken($tokenString)
    {
        return AuthToken::where('token', $tokenString)->where(function ($query) {
            $query->where('expires_at_expiry', 0)->orWhere(function ($query) {
                $query->where('expires_at_expiry', 1)->where('expires_at', '<', now());
            });
        })->delete();
    }

    protected function createUserFromToken($token)
    {
        try {
            $user_model = config('passwordless_auth.user_model', 'App\Models\User');

            return $user_model::create(array_merge([
                'email' => $token->email,
                'email_verified_at' => now(),
                'password' => bcrypt(Str::random(32)),
            ], $token->metadata ?: []));
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function validateTokenSecurity($token, array $options = [])
    {
        if (config('passwordless_auth.validate_ip') && isset($options['ip_address'])) {
            if ($token->ip_address !== $options['ip_address']) {
                return false;
            }
        }

        if (config('passwordless_auth.validate_user_agent') && isset($options['user_agent'])) {
            if ($token->user_agent !== $options['user_agent']) {
                return false;
            }
        }

        return true;
    }
}
