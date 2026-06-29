<?php

/**
 * File: app.php
 *
 * Bootstrap aplikasi Laravel — konfigurasi routing, middleware aliases,
 * dan penanganan exception untuk aplikasi apotek-digital.
 */

use App\Http\Middleware\EnsureAuthenticated;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    // Middleware aliases kustom untuk auth & role-based access
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth.apotek' => EnsureAuthenticated::class,
            'role' => RoleMiddleware::class,
        ]);
    })
    // Kirim response JSON untuk semua request ke prefix /api/*
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
