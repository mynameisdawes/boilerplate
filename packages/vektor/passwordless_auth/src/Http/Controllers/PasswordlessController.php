<?php

namespace Vektor\PasswordlessAuth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Vektor\PasswordlessAuth\Facades\PasswordlessAuth;

class PasswordlessController extends Controller
{
    public function showRegistrationForm()
    {
        return view('passwordless::register');
    }

    public function showLoginForm()
    {
        return view('passwordless::login');
    }

    public function authenticateWithToken(Request $request, $token)
    {
        $result = PasswordlessAuth::verifyToken($token, [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if (!$result['success']) {
            $message = match ($result['error']) {
                'invalid_token' => 'This link is invalid or has expired.',
                'security_validation_failed' => 'Security validation failed. Please request a new link.',
                'user_creation_failed' => 'Failed to create your account. Please try again.',
                default => 'Authentication failed. Please try again.'
            };

            return redirect()->route('passwordless.login')->with('error', $message);
        }

        Auth::logout();
        Auth::login($result['user']);
        PasswordlessAuth::deleteToken($token);
        $request->session()->regenerate();

        if ($result['created']) {
            session()->flash('success', 'Welcome! Your account has been created successfully.');
        }

        return redirect()->intended(config('passwordless_auth.redirect_to', '/'));
    }
}
