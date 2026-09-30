<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class EnsureSessionIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('EnsureSessionIsActive middleware started', [
                    'user_id' => auth()->id(),
                    'has_session' => $request->hasSession(),
                    'path' => $request->path(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                ]);

        if (Auth::guard('web')->check()) {
            Log::info('Session check', [
                'guard'       => 'web',
                'auth_check'  => Auth::guard('web')->check(),
                'user_id'     => Auth::guard('web')->id(),
                'session_id'  => $request->session()->getId(),
            ]);

            $sessionId = $request->session()->getId();

            $exists = \App\Models\UserLogins::where('user_id', Auth::guard('web')->id())
                ->where('session_id', $sessionId)
                ->where('is_active', true)
                ->exists();

            if (! $exists) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                abort(401, 'Session invalide');
            }
        }

        return $next($request);
    }
}