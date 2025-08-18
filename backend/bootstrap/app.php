<?php

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
        //
    })->create();
