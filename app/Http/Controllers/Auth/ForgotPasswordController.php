<?php

namespace App\Http\Controllers\Auth;

use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Vektor\Api\Http\Controllers\ApiController;

class ForgotPasswordController extends ApiController
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Where to redirect users after password reset email request.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Display the form to request a password reset link.
     */
    public function showLinkRequestForm(): View
    {
        return view('passwords_email');
    }

    /**
     * Display page to encourage user to look at their inbox.
     */
    public function showCheckEmailForm(): View
    {
        return view('passwords_check_email');
    }

    /**
     * Get the response for a successful password reset link.
     *
     * @return JsonResponse|RedirectResponse
     */
    protected function sendResetLinkResponse(Request $request, string $response)
    {
        return $this->response([
            'success' => true,
            'success_message' => trans($response),
            'data' => [
                'redirect_url' => route('password.email'),
            ],
        ]);
    }

    /**
     * Get the response for a failed password reset link.
     *
     * @throws ValidationException
     */
    protected function sendResetLinkFailedResponse(Request $request, string $response)
    {
        return $this->response([
            'error' => true,
            'error_message' => trans($response),
        ]);
    }
}
