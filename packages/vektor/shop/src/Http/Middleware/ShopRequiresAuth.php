<?php

namespace Vektor\Shop\Http\Middleware;

use Illuminate\Http\Request;
use Vektor\Api\Api;

class ShopRequiresAuth
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        if (
            false === config('shop.requires_auth')
            || (true === config('shop.requires_auth')) && \Auth::user()
        ) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            $api = new Api();

            return $api->response([
                'error' => true,
                'http_code' => 403,
            ]);
        }

        session(['url.intended' => url()->current()]);

        return redirect()->route('login');
    }
}
