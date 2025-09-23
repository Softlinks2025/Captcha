<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AuthenticateWithJwt
{
    public function handle(Request $request, Closure $next)
    {
        // Skip for login and logout routes to prevent redirect loops
        if ($request->is('login') || $request->is('logout') || $request->is('admin/login')) {
            return $next($request);
        }

        // Try to get the token from the Authorization header
        $token = $request->bearerToken();
        
        // If no token in header, try to get it from the session or cookie
        if (!$token) {
            $token = $request->session()->get('jwt_token') ?: $request->cookie('jwt_token');
            
            if ($token) {
                $request->headers->set('Authorization', 'Bearer ' . $token);
            }
        }

        if ($token) {
            try {
                $user = JWTAuth::parseToken()->authenticate();
                
                if ($user) {
                    // Set the user in the auth guard
                    auth()->setUser($user);
                    return $next($request);
                }
            } catch (TokenExpiredException $e) {
                // Token expired - clear the invalid token
                $request->session()->forget('jwt_token');
            } catch (TokenInvalidException $e) {
                // Invalid token - clear the invalid token
                $request->session()->forget('jwt_token');
            } catch (JWTException $e) {
                // Error processing token - clear the invalid token
                $request->session()->forget('jwt_token');
            }
        }

        // If we get here, the user is not authenticated
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        // Only redirect to login if we're not already trying to access the login page
        if (!$request->is('login')) {
            return redirect()->guest(route('login'))->with('error', 'Please login to access this page.');
        }

        return $next($request);
    }
}
