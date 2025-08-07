<?php

namespace Vektor\Shop\Http\Middleware;

use Illuminate\Http\Request;
use Vektor\Api\Api;

class ShopOnlyDisabled
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        if (false === config('shop.only')) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            $api = new Api();

            return $api->response([
                'error' => true,
                'http_code' => 404,
            ]);
        }

        return redirect()->route('base');
    }
}
