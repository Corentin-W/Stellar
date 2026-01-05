<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogApiAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('🔐 [LogApiAuth] Request received', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'has_session' => $request->hasSession(),
            'session_id' => $request->session()?->getId(),
            'auth_check_web' => Auth::guard('web')->check(),
            'auth_user_id' => Auth::guard('web')->id(),
            'cookies' => array_keys($request->cookies->all()),
            'headers' => [
                'Accept' => $request->header('Accept'),
                'Content-Type' => $request->header('Content-Type'),
                'X-CSRF-TOKEN' => $request->header('X-CSRF-TOKEN') ? 'present' : 'missing',
            ],
        ]);

        if (Auth::guard('web')->check()) {
            Log::info('✅ [LogApiAuth] User authenticated', [
                'user_id' => Auth::guard('web')->id(),
                'user_email' => Auth::guard('web')->user()->email,
            ]);
        } else {
            Log::warning('❌ [LogApiAuth] User NOT authenticated');
        }

        return $next($request);
    }
}
