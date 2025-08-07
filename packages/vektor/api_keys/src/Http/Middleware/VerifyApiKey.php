<?php

namespace Vektor\ApiKeys\Http\Middleware;

use Illuminate\Http\Request;
use Vektor\Api\Api;
use Vektor\ApiKeys\Models\ApiKey;

class VerifyApiKey
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        // Route::middleware(['api_verify_key'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])->group(function () {
        //     // Routes
        // });

        $api = new Api();

        try {
            $api_key = $request->header('X-API-KEY');

            if (!$api_key) {
                if ($request->expectsJson()) {
                    return $api->response([
                        'error' => true,
                        'error_message' => 'API key is missing',
                        'http_code' => 401,
                    ]);
                }
                abort(401);
            }

            $key = ApiKey::where('key', $api_key)->where('is_active', true)->first();

            if (!$key) {
                if ($request->expectsJson()) {
                    return $api->response([
                        'error' => true,
                        'error_message' => 'Invalid or expired API key',
                        'http_code' => 403,
                    ]);
                }
                abort(403);
            }

            return $next($request);
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return $api->response([
                    'error' => true,
                    'error_message' => $e->getMessage(),
                    'http_code' => 403,
                ]);
            }
            abort(403);
        }
    }
}
