<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        if ($request->user() && $request->user()->role === $role) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unauthorized - insufficient permissions'
        ], 403);
    }
}
