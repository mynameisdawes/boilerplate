<?php

namespace App\Http\Controllers\Auth;

use App\Providers\RouteServiceProvider;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Vektor\Api\Http\Controllers\ApiController;

class ResetPasswordController extends ApiController
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Display the password reset view for the given token.
     *
     * If no token is present, display the link request form.
     *
     * @return Factory|View
     */
    public function showResetForm(Request $request, ?string $token = null): View
    {
        return view('passwords_reset')->with([
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Get the response for a successful password reset.
     *
     * @return JsonResponse|RedirectResponse
     */
    protected function sendResetResponse(Request $request, string $response)
    {
        return $this->response([
            'success' => true,
            'success_message' => trans($response),
            'data' => [
                'redirect_url' => url($this->redirectPath()),
            ],
        ]);
    }

    /**
     * Get the response for a failed password reset.
     *
     * @return JsonResponse|RedirectResponse
     */
    protected function sendResetFailedResponse(Request $request, string $response)
    {
        return $this->response([
            'error' => true,
            'error_message' => trans($response),
        ]);
    }
}
