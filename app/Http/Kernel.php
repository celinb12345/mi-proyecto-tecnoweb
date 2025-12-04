<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * Middleware globales de la app.
     */
    protected $middleware = [
        // puedes dejar esto vacío o agregar los de siempre si ya los tenías
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class, 
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * Grupos de middleware (web / api).
     */
    protected $middlewareGroups = [
        'web' => [
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * Middleware individuales que puedes usar en rutas.
     */
    protected $routeMiddleware = [
        'verified'   => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'role'       => \App\Http\Middleware\RoleMiddleware::class, // 👈 nuestro middleware,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed'          => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle'        => \Illuminate\Routing\Middleware\ThrottleRequests::class,
    ];
}
