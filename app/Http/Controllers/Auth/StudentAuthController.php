<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StudentAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.student-login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'course' => 'required',
            'student_no' => 'required',
            'password' => 'required',
        ]);

        $credentials = $request->only('student_no', 'password');
        $selectedCourse = $request->input('course');

        if (Auth::guard('student')->attempt($credentials)) {
            $student = Auth::guard('student')->user();

            // Must be verified by a coordinator before using the system
            if ($student->isPendingVerification()) {
                Auth::guard('student')->logout();
                return back()->withErrors(['student_no' => 'Your account is pending verification by your coordinator. Please contact your OJT coordinator to verify that you belong to their class before you can log in.'])->withInput();
            }
            if ($student->isRejected()) {
                Auth::guard('student')->logout();
                return back()->withErrors(['student_no' => 'Your registration was not approved by your coordinator. Please contact your OJT coordinator if you believe this is an error.'])->withInput();
            }

            // Verify that the student's course matches the selected program
            if ($student->course !== $selectedCourse) {
                Auth::guard('student')->logout();
                return back()->withErrors(['course' => 'The selected program does not match your account.'])->withInput();
            }

            $request->session()->regenerate();
            return redirect()->route('student.dashboard')->with('success', 'Welcome student!');
        }

        // Helpful message when student number exists (so they don't try to register again)
        $studentExists = Student::where('student_no', $request->student_no)->exists();
        $message = $studentExists
            ? 'This student number is already registered. Please check your password and course selection, or use "Forgot password" if needed.'
            : 'Invalid credentials. Please check your student number or register for an account.';

        return back()->withErrors(['student_no' => $message])->withInput();
    }

    public function showRegisterForm()
    {
        return view('auth.student-register');
    }

    public function register(Request $request)
    {
        // Normalize for duplicate checks
        $studentNo = trim($request->student_no);
        $name = trim(preg_replace('/\s+/', ' ', $request->name ?? ''));

        $request->validate([
            'student_no' => [
                'required',
                'string',
                'max:50',
                Rule::unique('students', 'student_no'),
            ],
            'name' => 'required|string|max:255',
            'course' => 'required|string|max:100',
            'password' => 'required|confirmed|min:6',
            'face_encoding' => 'required|string',
        ], [
            'student_no.unique' => 'This student number is already registered. Please log in instead.',
        ]);

        // Explicit duplicate check with clear message (reinforces unique rule)
        if (Student::where('student_no', $studentNo)->exists()) {
            return back()->withErrors(['student_no' => 'This student number is already registered. Please log in instead.'])->withInput();
        }

        $student = Student::create([
            'student_no' => $studentNo,
            'name' => $name,
            'course' => $request->course,
            'password' => Hash::make($request->password),
            'face_encoding' => $request->face_encoding,
        ]);

        Auth::guard('student')->login($student);

        return redirect()->route('student.dashboard')->with('success', 'Registered successfully!');
    }

    public function logout(Request $request)
    {
        Auth::guard('student')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/')->with('success', 'Logged out successfully.');
    }

    /**
     * Show the form for the logged-in student to change their password.
     */
    public function showChangePasswordForm()
    {
        return view('auth.student-change-password');
    }

    /**
     * Update the logged-in student's password (current password required).
     */
    public function changePassword(Request $request)
    {
        $student = Auth::guard('student')->user();

        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'Please enter your current password.',
            'password.required' => 'Please enter a new password.',
            'password.confirmed' => 'The new password confirmation does not match.',
        ]);

        if (! Hash::check($request->current_password, $student->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
        }

        $student->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('student.dashboard')->with('success', 'Your password has been updated. You can continue using the dashboard.');
    }
}
