<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Coordinator;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UnifiedAuthController extends Controller
{
    /**
     * Show the unified login form. Redirect to dashboard if already logged in.
     */
    public function showLoginForm(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        if (Auth::guard('coordinator')->check()) {
            return redirect()->route('coordinator.dashboard');
        }
        if (Auth::guard('student')->check()) {
            return redirect()->route('student.dashboard');
        }

        return view('auth.unified-login');
    }

    /**
     * Handle login: try coordinator (by email) then student (by student_no), redirect to correct dashboard.
     */
    public function login(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string|max:255',
            'password' => 'required',
        ], [
            'identifier.required' => 'Please enter your email or student number.',
        ]);

        $identifier = trim($request->input('identifier'));
        $password = $request->input('password');

        $remember = $request->boolean('remember');

        // Try admin if identifier looks like email (case-insensitive)
        if (str_contains($identifier, '@')) {
            $admin = Admin::whereRaw('LOWER(email) = ?', [strtolower($identifier)])->first();
            if ($admin && Auth::guard('admin')->attempt(
                ['email' => $admin->email, 'password' => $password],
                $remember
            )) {
                $request->session()->regenerate();
                $admin->current_session_id = $request->session()->getId();
                $admin->save();

                return redirect()
                    ->route('admin.dashboard')
                    ->with('success', 'Welcome, '.$admin->name.'!');
            }

            $coordinator = Coordinator::whereRaw('LOWER(email) = ?', [strtolower($identifier)])->first();
            if ($coordinator && Auth::guard('coordinator')->attempt(
                ['email' => $coordinator->email, 'password' => $password],
                $remember
            )) {
                if (! ($coordinator->is_active ?? true)) {
                    Auth::guard('coordinator')->logout();

                    return back()
                        ->withErrors(['identifier' => 'Your coordinator account is inactive. Please contact an administrator.'])
                        ->withInput($request->only('identifier'));
                }
                $request->session()->regenerate();
                $coordinator->current_session_id = $request->session()->getId();
                $coordinator->save();

                return redirect()
                    ->route('coordinator.dashboard')
                    ->with('success', 'Welcome, '.$coordinator->name.'!');
            }
        }

        // Try student (by student number)
        $student = Student::where('student_no', $identifier)->first();
        if ($student && Auth::guard('student')->attempt(
            ['student_no' => $student->student_no, 'password' => $password],
            $remember
        )) {
            /** @var Student $user */
            $user = Auth::guard('student')->user();
            if ($user->isPendingVerification()) {
                Auth::guard('student')->logout();

                return back()
                    ->withErrors(['identifier' => 'Your account is pending verification by your coordinator. Please contact your OJT coordinator.'])
                    ->withInput($request->only('identifier'));
            }
            if ($user->isRejected()) {
                Auth::guard('student')->logout();

                return back()
                    ->withErrors(['identifier' => 'Your registration was not approved by your coordinator. Please contact your OJT coordinator if you believe this is an error.'])
                    ->withInput($request->only('identifier'));
            }
            $request->session()->regenerate();
            $user->current_session_id = $request->session()->getId();
            $user->save();

            return redirect()
                ->route('student.dashboard')
                ->with('success', 'Welcome!');
        }

        return back()
            ->withErrors(['identifier' => 'Invalid credentials. Please check your email or student number and password.'])
            ->withInput($request->only('identifier'));
    }
}
