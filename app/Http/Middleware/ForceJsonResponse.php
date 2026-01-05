<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * Force all API routes to return JSON responses
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Force Accept header to application/json
        $request->headers->set('Accept', 'application/json');

        $response = $next($request);

        // If the response is a redirect, convert it to JSON
        if ($response->isRedirect()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Please login',
                'redirect' => $response->headers->get('Location'),
            ], 401);
        }

        return $response;
    }
}
