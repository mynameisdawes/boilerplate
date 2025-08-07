<?php

namespace Vektor\Shop\Http\Middleware;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Vektor\Api\Api;

class ShopRequiresVerified
{
    /**
     * Handle an incoming request.
     *
     * @param null|mixed $redirectToRoute
     *
     * @return mixed
     */
    public function handle(Request $request, \Closure $next, $redirectToRoute = null)
    {
        if (true === config('shop.requires_auth')) {
            if (!$request->user() || ($request->user() instanceof MustVerifyEmail && !$request->user()->hasVerifiedEmail())) {
                if ($request->expectsJson()) {
                    $api = new Api();

                    return $api->response([
                        'error' => true,
                        'error_message' => 'Your email address is not verified.',
                        'http_code' => 403,
                    ]);
                }

                return Redirect::guest(URL::route($redirectToRoute ?: 'verification.notice'));
            }
        }

        return $next($request);
    }
}
