<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);

        $response = $next($request);

        // Ne pas logger les health checks pour eviter le bruit
        if ($request->is('api/health') || $request->is('up')) {
            return $response;
        }

        $duration = round((microtime(true) - $start) * 1000, 2);
        $status   = $response->getStatusCode();
        $level    = $status >= 500 ? 'error' : ($status >= 400 ? 'warning' : 'info');

        Log::$level('API Request', [
            'method'   => $request->method(),
            'url'      => $request->fullUrl(),
            'status'   => $status,
            'duration' => $duration . 'ms',
            'ip'       => $request->ip(),
            'user_id'  => optional($request->user())->id,
        ]);

        return $response;
    }
}
