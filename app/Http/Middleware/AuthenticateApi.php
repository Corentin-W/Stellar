<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApi
{
    /**
     * Handle an incoming request - API version that returns JSON instead of redirecting
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        Log::info('🔐 [AuthenticateApi] Checking authentication', [
            'guards' => $guards ?: ['web'],
            'has_session' => $request->hasSession(),
            'session_id' => $request->session()?->getId(),
        ]);

        $guards = empty($guards) ? ['web'] : $guards;

        foreach ($guards as $guard) {
            Log::info("🔍 [AuthenticateApi] Checking guard: {$guard}", [
                'is_authenticated' => Auth::guard($guard)->check(),
                'user_id' => Auth::guard($guard)->id(),
            ]);

            if (Auth::guard($guard)->check()) {
                Log::info("✅ [AuthenticateApi] User authenticated via guard: {$guard}", [
                    'user_id' => Auth::guard($guard)->id(),
                    'user_email' => Auth::guard($guard)->user()->email,
                ]);

                // Set the default guard for this request
                Auth::shouldUse($guard);

                return $next($request);
            }
        }

        Log::warning('❌ [AuthenticateApi] Authentication failed - returning JSON 401');

        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated. Please log in.',
        ], 401);
    }
}
