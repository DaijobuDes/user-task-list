<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Fix when unauthenticated users try to access API endpoints
        // with auth:sanctum middleware, if no
        // header Accept application/json is present
        $middleware->redirectGuestsTo(fn () => url('/'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        if (env("APP_DEBUG") === false) {
            if (request()->is('api/*')) {
                $exceptions->render(function (Throwable $te) {

                    $previous = $te->getPrevious();

                    if ($previous instanceof ModelNotFoundException) {
                        return response()->json(['message' => 'Not found.'], 404);
                    }

                    if ($previous instanceof AuthorizationException) {
                        return response()->json(['message' => 'Unauthorized'], 403);
                    }

                    return response()->json(['message' => 'Internal server error.'], 500);
                });
            }
        }
    })->create();
