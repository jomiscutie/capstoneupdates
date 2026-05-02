<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSingleSession
{
    /**
     * If the same account is logged in elsewhere, this session is no longer valid - log out and redirect.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
            if ($user->current_session_id !== null && $user->current_session_id !== $request->session()->getId()) {
                Auth::guard('admin')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('error', 'You have been logged out because your account was used on another device or browser. Please log in again.');
            }
        }

        if (Auth::guard('student')->check()) {
            $user = Auth::guard('student')->user();
            if ($user->current_session_id !== null && $user->current_session_id !== $request->session()->getId()) {
                Auth::guard('student')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('error', 'You have been logged out because your account was used on another device or browser. Please log in again.');
            }
        }

        if (Auth::guard('coordinator')->check()) {
            $user = Auth::guard('coordinator')->user();
            if ($user->current_session_id !== null && $user->current_session_id !== $request->session()->getId()) {
                Auth::guard('coordinator')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('error', 'You have been logged out because your account was used on another device or browser. Please log in again.');
            }
        }

        return $next($request);
    }
}
