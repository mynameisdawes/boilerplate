<?php

namespace Vektor\Api\Http\Middleware;

use Illuminate\Http\Request;
use Vektor\Api\Api;

class VerifyStatelessCsrfToken
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        $api = new Api();

        try {
            $api->validateRequest($request, config('api.salt'));

            return $next($request);
        } catch (\Exception $e) {
            return $api->response([
                'error' => true,
                'error_message' => $e->getMessage(),
                'http_code' => 403,
            ]);
        }
    }
}
