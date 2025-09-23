<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class JwtAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // First, ensure the user is authenticated
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            return redirect()->guest(route('login'));
        }

        // Check if the user has admin or subadmin role
        $user = auth()->user();
        if (!$user->hasAnyRole(['admin', 'push-subadmin', 'withdrawal-subadmin', 'ticket-manager'])) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Admin or Subadmin access required'], 403);
            }
            return redirect()->route('login')->with('error', 'Admin or Subadmin access required');
        }

        return $next($request);
    }
}