<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
            
            // Verify that the student's course matches the selected program
            if ($student->course !== $selectedCourse) {
                Auth::guard('student')->logout();
                return back()->withErrors(['course' => 'The selected program does not match your account.'])->withInput();
            }
            
            $request->session()->regenerate();
            return redirect()->route('student.dashboard')->with('success', 'Welcome student!');
        }

        return back()->withErrors(['student_no' => 'Invalid credentials.'])->withInput();
    }

    public function showRegisterForm()
    {
        return view('auth.student-register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'student_no' => 'required|unique:students,student_no',
            'name' => 'required',
            'course' => 'required',
            'password' => 'required|confirmed|min:6',
            'face_encoding' => 'required|string',
        ]);

        $student = Student::create([
            'student_no' => $request->student_no,
            'name' => $request->name,
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
}
