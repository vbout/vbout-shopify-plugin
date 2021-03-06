<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    public function handle($request, \Closure $next)
    {
        if (in_array(env('APP_ENV'), ['local', 'dev'])) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }
    protected $except = [
        'webhook/*'
    ];
}
