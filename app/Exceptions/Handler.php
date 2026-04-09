<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
        $this->reportable(function (Throwable $e): void {
            Log::error('Unhandled exception captured.', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);
        });

        $this->renderable(function (Throwable $e, Request $request) {
            if (!$request->expectsJson()) {
                return null;
            }

            if ($e instanceof HttpExceptionInterface) {
                return null;
            }

            if (!app()->isProduction()) {
                return null;
            }

            return response()->json([
                'code' => 500,
                'message' => '服务器开小差了，请稍后重试',
            ], 500);
        });
    }
}
