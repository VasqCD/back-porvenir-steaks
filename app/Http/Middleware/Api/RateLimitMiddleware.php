<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $limiterName = 'api', int $maxAttempts = 60): Response
    {
        // Configurar un identificador único para el limitador (combina IP + usuario si está autenticado)
        $key = $request->user() 
            ? $limiterName.':'.$request->user()->id
            : $limiterName.':'.$request->ip();

        // Intentar un incremento en el contador de rate limiting
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'message' => 'Too Many Attempts.',
                'retry_after' => RateLimiter::availableIn($key)
            ], 429);
        }
        
        // Incrementar el contador de solicitudes
        RateLimiter::hit($key);
        
        // Añadir encabezados de rate limiting a la respuesta
        $response = $next($request);
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => RateLimiter::remaining($key, $maxAttempts),
        ]);
        
        return $response;
    }
}