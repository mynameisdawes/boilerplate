<?php

namespace Vektor\Account\Http\Middleware;

use Illuminate\Http\Request;
use Vektor\Api\Api;

class AccountModuleEnabled
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        if (true === config('account.enabled')) {
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
