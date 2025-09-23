<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ForceJsonResponse
{
    public function handle(Request $request, Closure $next)
    {
        // Set Accept header to application/json
        $request->headers->set("Accept", "application/json");
        
        // Get the response
        $response = $next($request);
        
        // Force JSON response
        if (!$response instanceof JsonResponse) {
            $content = $response->getContent();
            $status = $response->status();
            $headers = $response->headers->all();
            
            $response = new JsonResponse(
                ["message" => $content],
                $status,
                $headers,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            );
        }
        
        // Set headers
        return $response
            ->header("Content-Type", "application/json")
            ->header("Access-Control-Allow-Origin", "*")
            ->header("Access-Control-Allow-Methods", "GET, POST, PUT, PATCH, DELETE, OPTIONS")
            ->header("Access-Control-Allow-Headers", "Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN");
    }
}