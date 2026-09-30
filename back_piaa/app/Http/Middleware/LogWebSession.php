<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\UserLogins;
use Illuminate\Support\Facades\Auth;

class LogWebSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('web')->check()) {

            $sessionId = $request->session()->getId();

            UserLogins::firstOrCreate(
                [
                    'user_id'    => Auth::guard('web')->id(),
                    'session_id' => $sessionId,
                ],
                [
                    'connection_type' => UserLogins::TYPE_ADMIN,
                    'ip_address'      => $request->ip(),
                    'user_agent'      => $request->userAgent(),
                    'logged_in_at'    => now(),
                    'is_active'       => true,
                ]
            );
        }

        return $next($request);
    }
}
