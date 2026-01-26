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

        return redirect()->route('coordinator.dashboard')
                         ->with('success', 'Registered and logged in successfully.');
    }

    // Logout
   public function logout(Request $request)
{
    Auth::guard('coordinator')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    // Redirect to login selector (instead of coordinator login)
    return redirect('/')
           ->with('success', 'You have logged out successfully.');
}

    // Dashboard
    public function dashboard()
    {
        $coordinator = Auth::guard('coordinator')->user();

        // Filter students by coordinator's course/program only
        // Only show students from the same program (course) as the coordinator
        $studentsQuery = \App\Models\Student::query();

        if (!empty($coordinator->major)) {
            // Coordinator's major field stores the course/program
            $studentsQuery->where('course', $coordinator->major);
        } else {
            // If coordinator has no course set, return empty result
            $studentsQuery->whereRaw('1 = 0'); // This ensures no students are returned
        }

        $students = $studentsQuery->get();

        $totalStudents = $students->count();

        $studentsTimedIn = \App\Models\Attendance::whereIn('student_id', $students->pluck('id'))
            ->where('date', now()->format('Y-m-d'))
            ->distinct('student_id')
            ->count('student_id');

        $studentsNotTimedIn = $totalStudents - $studentsTimedIn;

        // Count late arrivals today (both morning and afternoon)
        $lateArrivalsToday = \App\Models\Attendance::whereIn('student_id', $students->pluck('id'))
            ->where('date', now()->format('Y-m-d'))
            ->where(function($query) {
                $query->where('is_late', true)
                      ->orWhere('afternoon_is_late', true);
            })
            ->distinct('student_id')
            ->count('student_id');

        return view('coordinator.dashboard', compact('totalStudents', 'studentsTimedIn', 'studentsNotTimedIn', 'students', 'lateArrivalsToday'));
    }
}
