<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Response;
use Vektor\Api\Api;

class Handler extends ExceptionHandler
{
    /**
     * A list of exceptions with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, LogLevel::*>
     */
    protected $levels = [
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (\Throwable $e) {});
    }

    /**
     * Report or log an exception.
     *
     * @throws \Throwable
     */
    public function report(\Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Throwable
     */
    public function render($request, \Throwable $exception)
    {
        if ($request->expectsJson()) {
            if ($exception instanceof ValidationException) {
                $this->api = new Api();

                return $this->api->response([
                    'error' => true,
                    'error_message' => $exception->getMessage(),
                    'http_code' => $exception->status,
                    'data' => [
                        'errors' => $exception->errors(),
                    ],
                ]);
            }
        }

        return parent::render($request, $exception);
    }
}
