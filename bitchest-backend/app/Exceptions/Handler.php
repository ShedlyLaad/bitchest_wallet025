<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ValidationException) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $exception->errors()
            ], 422);
        }

        if ($exception instanceof ModelNotFoundException) {
            $model = class_basename($exception->getModel());
            return response()->json([
                'message' => 'Resource not found',
                'error' => "The requested {$model} resource does not exist."
            ], 404);
        }

        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'message' => 'Route not found',
                'error' => 'The requested endpoint does not exist.',
                'path' => $request->path()
            ], 404);
        }

        if ($exception instanceof \InvalidArgumentException) {
            return response()->json([
                'message' => $exception->getMessage(),
                'error' => $exception->getMessage()
            ], 400);
        }

        if ($exception instanceof QueryException) {
            \Log::error('Database error', [
                'message' => $exception->getMessage(),
                'sql' => $exception->getSql() ?? 'N/A',
                'bindings' => $exception->getBindings() ?? [],
                'path' => $request->path()
            ]);

            if (config('app.debug')) {
                return response()->json([
                    'message' => 'Database error',
                    'error' => $exception->getMessage()
                ], 500);
            }

            return response()->json([
                'message' => 'Database error. Please try again later.'
            ], 500);
        }

        if (method_exists($exception, 'getStatusCode')) {
            $status = $exception->getStatusCode();
            return response()->json([
                'message' => $exception->getMessage() ?: 'An error occurred',
                'error' => $exception->getMessage() ?: 'An error occurred'
            ], $status);
        }

        if (config('app.debug')) {
            return response()->json([
                'message' => $exception->getMessage(),
                'error' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ], 500);
        }

        return response()->json([
            'message' => 'An error occurred. Please try again later.',
            'error' => 'Server error'
        ], 500);
    }
}
