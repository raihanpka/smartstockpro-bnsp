<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMonitor
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $response = $next($request);
        $duration = (microtime(true) - $startTime) * 1000;

        if ($duration > 1000) {
            \Illuminate\Support\Facades\Log::warning('Slow request detected', [
                'url' => $request->fullUrl(),
                'duration_ms' => round($duration, 2),
                'ip' => $request->ip(),
            ]);
        }

        return $response;
    }
}
