<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Vektor\Api\Http\Controllers\ApiController;
use Vektor\Utilities\Formatter;

class RegisterController extends ApiController
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
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
        $this->middleware('guest');
    }

    /**
     * Show the application registration form.
     */
    public function showRegistrationForm(): View
    {
        return view('register');
    }

    /**
     * The user has been registered.
     *
     * @param mixed $user
     *
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
        return $this->response([
            'success' => true,
            'success_message' => 'Your have registered successfully',
            'data' => [
                'redirect_url' => url($this->redirectTo),
            ],
        ]);
    }

    /**
     * Get a validator for an incoming registration request.
     */
    protected function validator(array $data): ValidatorContract
    {
        return Validator::make($data, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'shipping_country' => ['string'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     */
    protected function create(array $data): User
    {
        $user_data = [
            'first_name' => Formatter::name($data['first_name']),
            'last_name' => Formatter::name($data['last_name']),
            'email' => Formatter::email($data['email']),
            'password' => Hash::make($data['password']),
            'configuration' => [],
        ];

        if (isset($data['shipping_country']) && !empty($data['shipping_country'])) {
            $user_data['configuration']['shipping_country'] = $data['shipping_country'];
        }

        return User::create($user_data);
    }
}
