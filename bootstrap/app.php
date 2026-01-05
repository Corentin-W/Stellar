<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Enregistrement des middlewares
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'locale' => \App\Http\Middleware\LocaleMiddleware::class,
            'subscription.required' => \App\Http\Middleware\RequireActiveSubscription::class,
            'feature.access' => \App\Http\Middleware\CheckFeatureAccess::class,
            'force.json' => \App\Http\Middleware\ForceJsonResponse::class,
        ]);

        // Appliquer le middleware de locale à toutes les routes web
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\LocaleMiddleware::class,
        ]);

        // Appliquer les middlewares aux routes API (incluant session pour auth:web)
        $middleware->appendToGroup('api', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            // ForceJsonResponse removed temporarily for debugging
        ]);
    })
    ->withEvents(discover: [
        __DIR__.'/../app/Listeners',
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
