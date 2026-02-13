<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfOtpVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && $user->is_otp_verified) {
            return redirect()->route(match ($user->role) {
                'admin' => 'admin.dashboard',
                'user' => 'user.dashboard',
                default => 'dashboard',
            });
        }
        return $next($request);
    }
}
