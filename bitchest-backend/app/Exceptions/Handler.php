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
        // Gestion des erreurs de validation (422)
        if ($exception instanceof ValidationException) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $exception->errors()
            ], 422);
        }

        // Gestion des erreurs de modèle non trouvé (404)
        if ($exception instanceof ModelNotFoundException) {
            $model = class_basename($exception->getModel());
            return response()->json([
                'message' => 'Ressource introuvable',
                'error' => "La ressource {$model} demandée n'existe pas."
            ], 404);
        }

        // Gestion des erreurs de route non trouvée (404)
        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'message' => 'Route introuvable',
                'error' => 'L\'endpoint demandé n\'existe pas.',
                'path' => $request->path()
            ], 404);
        }

        // Gestion des erreurs InvalidArgumentException (400)
        if ($exception instanceof \InvalidArgumentException) {
            return response()->json([
                'message' => $exception->getMessage(),
                'error' => $exception->getMessage()
            ], 400);
        }

        // Gestion des erreurs de base de données
        if ($exception instanceof QueryException) {
            \Log::error('Database error', [
                'message' => $exception->getMessage(),
                'sql' => $exception->getSql() ?? 'N/A',
                'bindings' => $exception->getBindings() ?? [],
                'path' => $request->path()
            ]);

            // En production, ne pas exposer les détails de la base de données
            if (config('app.debug')) {
                return response()->json([
                    'message' => 'Erreur de base de données',
                    'error' => $exception->getMessage()
                ], 500);
            }

            return response()->json([
                'message' => 'Erreur de base de données. Veuillez réessayer plus tard.'
            ], 500);
        }

        // Gestion des autres erreurs HTTP
        if (method_exists($exception, 'getStatusCode')) {
            $status = $exception->getStatusCode();
            return response()->json([
                'message' => $exception->getMessage() ?: 'Une erreur est survenue',
                'error' => $exception->getMessage() ?: 'Une erreur est survenue'
            ], $status);
        }

        // En mode debug, afficher plus de détails
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
            'message' => 'Une erreur est survenue. Veuillez réessayer plus tard.',
            'error' => 'Erreur serveur'
        ], 500);
    }
}
