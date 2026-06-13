<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureAuthenticated;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Alias utilisable dans les routes : ->middleware('auth.code')
        $middleware->alias([
            'auth.code' => EnsureAuthenticated::class,
        ]);

        // Exclure les webhooks Twilio de la vérification CSRF
        $middleware->validateCsrfTokens(except: [
            'twilio/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();