<?php

namespace Vektor\PasswordlessAuth\Http\Controllers\Api;

use Illuminate\Http\Request;
use Vektor\Api\Http\Controllers\ApiController;
use Vektor\PasswordlessAuth\Facades\PasswordlessAuth;
use Vektor\Utilities\Formatter;

class PasswordlessController extends ApiController
{
    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
        ]);

        try {
            $user_data = [
                'first_name' => Formatter::name($request->input('first_name')),
                'last_name' => Formatter::name($request->input('last_name')),
            ];

            PasswordlessAuth::sendRegistrationLink(
                Formatter::email($request->input('email')),
                $user_data,
                [
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            );

            return $this->response([
                'success' => true,
                'success_message' => 'A login link has been sent to your email.',
            ]);
        } catch (\Exception $e) {
            return $this->response([
                'error' => true,
                'error_message' => 'Failed to send login link. Please try again.',
                'http_code' => 500,
            ]);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $user_model = config('passwordless_auth.user_model', 'App\Models\User');
            $user = $user_model::where('email', $request->input('email'))->first();

            if (!$user) {
                return $this->response([
                    'success' => true,
                    'success_message' => 'If an account exists with this email, you will receive a login link.',
                ]);
            }

            PasswordlessAuth::sendLoginLink($user, [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return $this->response([
                'success' => true,
                'success_message' => 'A login link has been sent to your email.',
            ]);
        } catch (\Exception $e) {
            return $this->response([
                'error' => true,
                'error_message' => 'Failed to send login link. Please try again.',
                'http_code' => 500,
            ]);
        }
    }
}
