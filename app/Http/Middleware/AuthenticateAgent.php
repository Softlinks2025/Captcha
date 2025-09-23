<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AuthenticateAgent extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {
        // Disable error reporting to prevent output
        error_reporting(0);
        ini_set('display_errors', 0);
        
        // Start output buffering to prevent headers already sent error
        ob_start();
        
        try {
            // Set the guard to agent
            $guards = ['agent'];
            
            if (!$this->auth->guard('agent')->check()) {
                ob_end_clean();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Please login as agent.'
                ], 401);
            }

            ob_end_clean();
            return $next($request);
        } catch (TokenExpiredException $e) {
            ob_end_clean();
            return response()->json([
                'status' => 'error',
                'message' => 'Token has expired'
            ], 401);
        } catch (TokenInvalidException $e) {
            ob_end_clean();
            return response()->json([
                'status' => 'error',
                'message' => 'Token is invalid'
            ], 401);
        } catch (JWTException $e) {
            ob_end_clean();
            return response()->json([
                'status' => 'error',
                'message' => 'Token could not be parsed'
            ], 401);
        } catch (\Exception $e) {
            ob_end_clean();
            return response()->json([
                'status' => 'error',
                'message' => 'Authentication failed'
            ], 401);
        }
    }
} 