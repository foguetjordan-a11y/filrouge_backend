<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckApproved
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Votre compte doit être approuvé par l’admin pour continuer.'
            ], 403);
        }

        return $next($request);
    }
}
