<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Force JSON accept header for API requests
        $request->headers->set('Accept', 'application/json');
        
        // Get the response
        $response = $next($request);
        
        // Only process API routes
        if ($request->is('api/*')) {
            // Set JSON content type header
            $response->headers->set('Content-Type', 'application/json');
            
            // If the response is not already JSON, convert it
            if (!$response instanceof \Illuminate\Http\JsonResponse) {
                $content = $response->getContent();
                $status = $response->getStatusCode();
                
                // Try to decode the content if it's JSON
                $decoded = json_decode($content, true);
                
                // If it's already JSON, use it as is, otherwise wrap it
                $data = json_last_error() === JSON_ERROR_NONE 
                    ? $decoded 
                    : ['data' => $content];
                
                $response = response()->json($data, $status);
            }
            
            // Add CORS headers
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        }
        
        return $response;
    }
}
