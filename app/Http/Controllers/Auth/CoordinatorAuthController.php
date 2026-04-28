<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\ProgramAlias;
use Illuminate\Http\Request;
use App\Models\Coordinator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class CoordinatorAuthController extends Controller
{
    // Show login form
    public function showLoginForm()
    {
        return view('auth.coordinator-login');
    }

    // Handle login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::guard('coordinator')->attempt($credentials)) {
            $coordinator = Auth::guard('coordinator')->user();

            if (! ($coordinator->is_active ?? true)) {
                Auth::guard('coordinator')->logout();
                return back()->withErrors(['email' => 'Your coordinator account is inactive. Please contact an administrator.'])
                    ->withInput();
            }

            $request->session()->regenerate();
            $coordinator->current_session_id = $request->session()->getId();
            $coordinator->save();

            // Redirect to dashboard with welcome message
            return redirect()->route('coordinator.dashboard')
                             ->with('success', 'Welcome, ' . $coordinator->name . '!');
        }

        // Failed login
        return back()->withErrors(['email' => 'Invalid email address or password.'])
                     ->withInput();
    }

    // Show registration form
    public function showRegisterForm()
    {
        return redirect()->route('login')
            ->with('info', 'Coordinator accounts are now created and managed by the admin.');
    }

    // Handle registration
    public function register(Request $request)
    {
        return redirect()->route('login')
            ->with('info', 'Coordinator accounts are now created and managed by the admin.');
    }

    // Logout
   public function logout(Request $request)
{
    $coordinator = Auth::guard('coordinator')->user();
    if ($coordinator) {
        $coordinator->current_session_id = null;
        $coordinator->save();
    }
    Auth::guard('coordinator')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/')
           ->with('success', 'You have logged out successfully.');
}

    // Dashboard
    public function dashboard()
    {
        $coordinator = Auth::guard('coordinator')->user();
        $students = \App\Models\Student::forCoordinator($coordinator)->verified()->get();
        $pendingVerificationCount = \App\Models\Student::forCoordinator($coordinator)->pendingVerification()->count();
        $assignedPrograms = $coordinator->assignments()
            ->pluck('course')
            ->map(fn ($course) => ProgramAlias::normalizeCourse(trim((string) $course)))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        if ($assignedPrograms->isEmpty() && !empty($coordinator->major)) {
            $assignedPrograms = collect([ProgramAlias::normalizeCourse(trim((string) $coordinator->major))])
                ->filter()
                ->values();
        }

        $totalStudents = $students->count();

        $studentIds = $students->pluck('id');
        $today = now()->format('Y-m-d');

        $studentIdsPresentToday = \App\Models\Attendance::valid()->whereIn('student_id', $studentIds)
            ->where('date', $today)
            ->distinct()
            ->pluck('student_id');

        $studentsTimedIn = $studentIdsPresentToday->count();
        $studentsNotTimedIn = $totalStudents - $studentsTimedIn;

        // Count late arrivals today (both morning and afternoon)
        $lateArrivalsToday = \App\Models\Attendance::valid()->whereIn('student_id', $studentIds)
            ->where('date', $today)
            ->where(function($query) {
                $query->where('is_late', true)
                      ->orWhere('afternoon_is_late', true);
            })
            ->distinct('student_id')
            ->count('student_id');

        return view('coordinator.dashboard', compact(
            'totalStudents',
            'studentsTimedIn',
            'studentsNotTimedIn',
            'students',
            'lateArrivalsToday',
            'pendingVerificationCount',
            'assignedPrograms'
        ));
    }

    /**
     * Full-page list of students not yet timed in today (absent today).
     */
    public function absentToday()
    {
        $coordinator = Auth::guard('coordinator')->user();
        $studentIds = \App\Models\Student::forCoordinator($coordinator)->verified()->pluck('id');
        $today = now()->format('Y-m-d');

        $studentIdsPresentToday = \App\Models\Attendance::valid()->whereIn('student_id', $studentIds)
            ->where('date', $today)
            ->distinct()
            ->pluck('student_id');

        $absentTodayStudents = \App\Models\Student::forCoordinator($coordinator)
            ->verified()
            ->whereNotIn('id', $studentIdsPresentToday)
            ->orderBy('name')
            ->get();

        return view('coordinator.absent-today', compact('absentTodayStudents'));
    }
}
