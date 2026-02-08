<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
            'course' => 'required',
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $selectedCourse = $request->input('course');

        if (Auth::guard('coordinator')->attempt($credentials)) {
            $coordinator = Auth::guard('coordinator')->user();
            
            // Verify that the coordinator's course matches the selected program
            // Note: coordinators store program in 'major' field, but we match it with student's 'course'
            if ($coordinator->major !== $selectedCourse) {
                Auth::guard('coordinator')->logout();
                return back()->withErrors(['course' => 'The selected program does not match your account.'])->withInput();
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
        return view('auth.coordinator-register');
    }

    // Handle registration
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:coordinators,email',
            'password' => 'required|confirmed|min:6',
            'college' => 'required',
            'course' => 'required',
        ]);

        $coordinator = Coordinator::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'college' => $request->college,
            'major' => $request->course, // Store course in major field for coordinators
        ]);

        Auth::guard('coordinator')->login($coordinator);
        $request->session()->regenerate();
        $coordinator->current_session_id = $request->session()->getId();
        $coordinator->save();

        return redirect()->route('coordinator.dashboard')
                         ->with('success', 'Registered and logged in successfully.');
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

        $totalStudents = $students->count();

        $studentIds = $students->pluck('id');
        $today = now()->format('Y-m-d');

        $studentIdsPresentToday = \App\Models\Attendance::whereIn('student_id', $studentIds)
            ->where('date', $today)
            ->distinct()
            ->pluck('student_id');

        $studentsTimedIn = $studentIdsPresentToday->count();
        $studentsNotTimedIn = $totalStudents - $studentsTimedIn;

        $absentTodayStudents = \App\Models\Student::forCoordinator($coordinator)
            ->verified()
            ->whereNotIn('id', $studentIdsPresentToday)
            ->orderBy('name')
            ->get();

        // Count late arrivals today (both morning and afternoon)
        $lateArrivalsToday = \App\Models\Attendance::whereIn('student_id', $studentIds)
            ->where('date', $today)
            ->where(function($query) {
                $query->where('is_late', true)
                      ->orWhere('afternoon_is_late', true);
            })
            ->distinct('student_id')
            ->count('student_id');

        return view('coordinator.dashboard', compact('totalStudents', 'studentsTimedIn', 'studentsNotTimedIn', 'students', 'lateArrivalsToday', 'pendingVerificationCount', 'absentTodayStudents'));
    }

    /**
     * Full-page list of students not yet timed in today (absent today).
     */
    public function absentToday()
    {
        $coordinator = Auth::guard('coordinator')->user();
        $studentIds = \App\Models\Student::forCoordinator($coordinator)->verified()->pluck('id');
        $today = now()->format('Y-m-d');

        $studentIdsPresentToday = \App\Models\Attendance::whereIn('student_id', $studentIds)
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
