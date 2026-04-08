<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
   ->withMiddleware(function (Middleware $middleware) {

    $middleware->alias([
        'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
        'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        'check.role' => \App\Http\Middleware\CheckRole::class,
        'check.approved' => \App\Http\Middleware\CheckApproved::class,
    ]);

    // Ajouter CORS pour toutes les routes API
    $middleware->api(prepend: [
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        \App\Http\Middleware\LogApiRequests::class,
    ]);

})

    ->withExceptions(function (Exceptions $exceptions): void {
        // Logger toutes les exceptions non-gérées
        $exceptions->report(function (\Throwable $e) {
            if (!($e instanceof \Illuminate\Validation\ValidationException)
                && !($e instanceof \Illuminate\Auth\AuthenticationException)
                && !($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException)) {
                \Log::error('Exception non geree: ' . $e->getMessage(), [
                    'exception' => get_class($e),
                    'file'      => $e->getFile(),
                    'line'      => $e->getLine(),
                    'trace'     => $e->getTraceAsString(),
                ]);
            }
        });

        // Réponse JSON uniforme pour les erreurs API
        $exceptions->render(function (\Throwable $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreur de validation',
                        'errors'  => $e->errors(),
                    ], 422);
                }

                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Non authentifie',
                    ], 401);
                }

                if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ressource introuvable',
                    ], 404);
                }

                if ($e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Methode HTTP non autorisee',
                    ], 405);
                }

                // Erreur générique — ne pas exposer les détails en prod
                $debug = config('app.debug');
                return response()->json([
                    'success' => false,
                    'message' => $debug ? $e->getMessage() : 'Erreur interne du serveur',
                    'error'   => $debug ? get_class($e) : null,
                ], 500);
            }
        });
    })->create();
