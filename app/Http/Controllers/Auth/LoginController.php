<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Vektor\Api\Http\Controllers\ApiController;

class LoginController extends ApiController
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('guest')->except(['logout', 'isLoggedIn', 'matches', 'verify']);
    }

    /**
     * Show the application's login form.
     */
    public function showLoginForm(): View
    {
        return view('login');
    }

    /**
     * Get the logged in state of the application.
     *
     * @return mixed
     */
    public function isLoggedIn()
    {
        $user = \Auth::user();

        return $this->response([
            'success' => true,
            'success_message' => $user ? 'The user is logged in' : 'The user is not logged in',
            'data' => [
                'is_logged_in' => $user ? true : false,
            ],
        ]);
    }

    /**
     * Get the user existence state.
     *
     * @return mixed
     */
    public function exists(Request $request)
    {
        $user = User::where('email', $request->input('email'))->first();

        return $this->response([
            'success' => true,
            'success_message' => $user ? 'The user exists' : 'The user does not exist',
            'data' => [
                'exists' => $user ? true : false,
            ],
        ]);
    }

    /**
     * Get the user existence state.
     *
     * @return mixed
     */
    public function matches(Request $request)
    {
        if (\Auth::check() && \Auth::user()->email == $request->input('email')) {
            return $this->response([
                'success' => true,
                'success_message' => 'Authed user matches email',
                'data' => [
                    'exists' => null,
                ],
            ]);
        }

        return $this->exists($request);
    }

    /**
     * Get the user existence state.
     *
     * @return mixed
     */
    public function verify(Request $request)
    {
        return $this->response([
            'success' => \Auth::guard()->validate($this->credentials($request)),
        ]);
    }

    /**
     * The user has been authenticated.
     *
     * @param mixed $user
     *
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        return $this->response([
            'success' => true,
            'success_message' => 'You have logged in successfully',
            'data' => [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'stripe_id' => isset($user->stripe_id) ? $user->stripe_id : null,
                'redirect_url' => redirect()->intended($this->redirectTo)->getTargetUrl(),
                'redirect_type' => 'get',
            ],
        ]);
    }

    /**
     * Get the failed login response instance.
     *
     * @throws ValidationException
     */
    protected function sendFailedLoginResponse(Request $request): Response
    {
        return $this->response([
            'error' => true,
            'error_message' => trans('auth.failed'),
        ]);
    }
}
