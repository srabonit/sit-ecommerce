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
        // Middleware alias mapping
        $middleware->alias([
            'admin'             => \App\Http\Middleware\AdminMiddleware::class,
            'check.permission'  => \App\Http\Middleware\CheckPermission::class,
            'branch.scope'      => \App\Http\Middleware\EnsureBranchAccess::class,
            'security.firewall' => \App\Http\Middleware\SecurityFirewallMiddleware::class,
            'security.tracker'  => \App\Http\Middleware\SecurityActivityTrackerMiddleware::class,
        ]);

        // Global Web Middleware Group
        $middleware->web(append: [
            \App\Http\Middleware\SecurityFirewallMiddleware::class,
            \App\Http\Middleware\TrackUserSession::class,
            \App\Http\Middleware\SecurityActivityTrackerMiddleware::class,
        ]);

        // Global API Middleware Group
        $middleware->api(append: [
            \App\Http\Middleware\SecurityFirewallMiddleware::class,
            \App\Http\Middleware\SecurityActivityTrackerMiddleware::class,
        ]);

        // CSRF Token Exceptions
        $middleware->validateCsrfTokens(except: [
            '/payment/sslcommerz/*',
            '/payment/shurjopay/*',
            '/payment/eps/*',
            '/payment/stripe/webhook',
            '/webhook/courier/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();