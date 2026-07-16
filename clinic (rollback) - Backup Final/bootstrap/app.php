<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
        'auth' => \App\Http\Middleware\Authenticate::class,
        'role' => \App\Http\Middleware\RoleMiddleware::class,
        'otp.verified' => \App\Http\Middleware\EnsureOtpIsVerified::class,
        'otp.not.verified' => \App\Http\Middleware\RedirectIfOtpVerified::class,
        'role.redirect' => \App\Http\Middleware\RedirectIfAuthenticatedWithRole::class,
        'prevent-back-history' => \App\Http\Middleware\PreventBackHistory::class,
    ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
