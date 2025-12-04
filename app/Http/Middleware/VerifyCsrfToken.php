<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * IMPORTANTE:
     * NO iniciar con '/'.
     */
    protected $except = [
        'cliente/pagos/payment/callback', // <-- AQUI TU CALLBACK
    ];
}
