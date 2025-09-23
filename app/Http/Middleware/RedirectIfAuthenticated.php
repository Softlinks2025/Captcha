<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                
                // If this is an AJAX request, just return a JSON response
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Already authenticated',
                        'redirect' => true
                    ], 403);
                }
                
                // Don't redirect if already on the intended page
                $currentRoute = $request->route()->getName();
                $intendedRoutes = [
                    'admin.push-notification.index',
                    'admin.withdrawal-requests.index',
                    'admin.tickets.index',
                    'admin.dashboard',
                    'admin.logout',
                    'logout'
                ];
                
                if (in_array($currentRoute, $intendedRoutes)) {
                    return $next($request);
                }
                
                // Redirect based on user role
                if ($user->hasRole('push-subadmin')) {
                    return redirect()->route('admin.push-notification.index');
                } elseif ($user->hasRole('withdrawal-subadmin')) {
                    return redirect()->route('admin.withdrawal-requests.index');
                } elseif ($user->hasRole('ticket-manager')) {
                    return redirect()->route('admin.tickets.index');
                } else {
                    // Default redirect for admin and other roles
                    return redirect()->route('admin.dashboard');
                }
            }
        }

        return $next($request);
    }
}
