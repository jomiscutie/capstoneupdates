<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentTermAssignment;
use App\Support\Services\FaceEncodingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            'student_no' => 'required',
            'password' => 'required',
        ]);

        $credentials = $request->only('student_no', 'password');

        if (Auth::guard('student')->attempt($credentials)) {
            $student = Auth::guard('student')->user();

            if ($student->isPendingVerification()) {
                Auth::guard('student')->logout();

                return back()->withErrors(['student_no' => 'Your account is pending verification by your coordinator. Please contact your OJT coordinator to verify that you belong to their class before you can log in.'])->withInput();
            }
            if ($student->isRejected()) {
                Auth::guard('student')->logout();

                return back()->withErrors(['student_no' => 'Your registration was not approved by your coordinator. Please contact your OJT coordinator if you believe this is an error.'])->withInput();
            }

            $request->session()->regenerate();
            $student->current_session_id = $request->session()->getId();
            $student->save();

            return redirect()->route('student.dashboard')->with('success', 'Welcome student!');
        }

        // Helpful message when student number exists (so they don't try to register again)
        $studentExists = Student::where('student_no', $request->student_no)->exists();
        $message = $studentExists
            ? 'Invalid password for this student number. Please try again or use "Forgot password" if needed.'
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
        $lastName = trim(preg_replace('/\s+/', ' ', (string) ($request->last_name ?? '')));
        $firstName = trim(preg_replace('/\s+/', ' ', (string) ($request->first_name ?? '')));
        $middleName = trim(preg_replace('/\s+/', ' ', (string) ($request->middle_name ?? '')));
        $suffix = strtoupper(trim((string) ($request->suffix ?? '')));
        $selectedProgram = trim((string) ($request->course ?? ''));
        $selectedMajor = trim((string) ($request->major ?? ''));
        $programOptions = Student::getProgramOptions();
        $officeOptions = Student::getOfficeOptions();
        $sectionOptions = array_values(array_filter(
            Student::getSectionOptions(),
            static function ($value): bool {
                $section = trim((string) $value);

                return strcasecmp($section, 'All') !== 0
                    && strcasecmp($section, 'Section All') !== 0;
            }
        ));
        $fullName = trim(preg_replace('/\s+/', ' ', implode(' ', array_filter([
            $firstName,
            $middleName,
            $lastName,
            $suffix,
        ]        ))));

        $rawOfficeSelection = trim((string) $request->input('assigned_office', ''));
        $request->merge([
            'assigned_office' => $rawOfficeSelection === '' ? null : $rawOfficeSelection,
        ]);

        $request->validate([
            'student_no' => [
                'required',
                'string',
                'max:50',
                Rule::unique('students', 'student_no'),
            ],
            // Names: no digits (Unicode-aware with /u). Middle name may be empty.
            'last_name' => ['required', 'string', 'max:100', 'regex:/^\D+$/u'],
            'first_name' => ['required', 'string', 'max:100', 'regex:/^\D+$/u'],
            'middle_name' => ['nullable', 'string', 'max:100', 'regex:/^\D*$/u'],
            'suffix' => 'nullable|string|max:20',
            'course' => ['required', 'string', 'max:100', Rule::in($programOptions)],
            'major' => 'nullable|string|max:100',
            'school_year' => ['required', 'string', 'max:20', 'regex:/^\d{4}-\d{4}$/'],
            'term' => ['required', Rule::in(Student::TERMS)],
            'section' => ['required', Rule::in($sectionOptions)],
            'assigned_office' => ['nullable', Rule::in($officeOptions)],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'face_encoding' => 'nullable|string',
        ], [
            'student_no.unique' => 'This student number is already registered. Please log in instead.',
            'school_year.regex' => 'School year must be in the format YYYY-YYYY, like 2026-2027.',
            'first_name.regex' => 'First name cannot contain numbers.',
            'middle_name.regex' => 'Middle name cannot contain numbers.',
            'last_name.regex' => 'Last name cannot contain numbers.',
        ]);

        if (Student::where('student_no', $studentNo)->exists()) {
            return back()->withErrors(['student_no' => 'This student number is already registered. Please log in instead.'])->withInput();
        }

        $allowedMajors = Student::majorsForProgram($selectedProgram);

        if ($allowedMajors !== []) {
            if ($selectedMajor === '') {
                return back()->withErrors(['major' => 'Please select a major for the selected program.'])->withInput();
            }

            if (! in_array($selectedMajor, $allowedMajors, true)) {
                return back()->withErrors(['major' => 'The selected major is not valid for the chosen program.'])->withInput();
            }
        } elseif ($selectedMajor !== '') {
            return back()->withErrors(['major' => 'This program does not require a major.'])->withInput();
        }

        // Reject if this face is already registered to another account (same face, different name/ID)
        // Can be disabled via FACE_DUPLICATE_CHECK=false if causing too many false positives
        $faceEncoding = trim((string) $request->input('face_encoding', ''));

        if ($faceEncoding !== '' && config('services.face_duplicate_check', true)) {
            $newEncoding = json_decode($faceEncoding, true);
            if (is_array($newEncoding) && count($newEncoding) === FaceEncodingService::ENCODING_LENGTH) {
                $existingWithFace = Student::whereNotNull('face_encoding')->get();
                foreach ($existingWithFace as $existing) {
                    $stored = json_decode($existing->face_encoding, true);
                    if (is_array($stored) && FaceEncodingService::isSamePerson($newEncoding, $stored)) {
                        return back()->withErrors(['face_encoding' => 'This face is already registered to another account. One person cannot register with multiple names or student numbers.'])->withInput();
                    }
                }
            }
        }

        $student = null;

        DB::transaction(function () use ($request, $studentNo, $fullName, $lastName, $firstName, $middleName, $suffix, $selectedMajor, $faceEncoding, &$student) {
            $student = Student::create([
                'student_no' => $studentNo,
                'name' => $fullName,
                'last_name' => $lastName,
                'first_name' => $firstName,
                'middle_name' => $middleName !== '' ? $middleName : null,
                'suffix' => $suffix !== '' ? $suffix : null,
                'course' => $request->course,
                'major' => $selectedMajor !== '' ? $selectedMajor : null,
                'section' => $request->section,
                'assigned_office' => $request->assigned_office,
                'password' => Hash::make($request->password),
                'face_encoding' => $faceEncoding !== '' ? $faceEncoding : null,
            ]);

            $student->termAssignments()->create([
                'course' => $request->course,
                'school_year' => $request->school_year,
                'term' => $request->term,
                'section' => $request->section,
                'required_ojt_hours' => (float) config('dtr.default_required_hours', 120),
                'status' => StudentTermAssignment::STATUS_ACTIVE,
                'started_at' => now(),
            ]);
        });

        // Do NOT log in - redirect to login page with pending verification message.
        // If face was skipped, include a clear reminder so coordinator can assist later.
        $message = $faceEncoding === ''
            ? 'Registration submitted without face enrollment. Wait for your coordinator to verify your account. You may use password verification while your camera is unavailable.'
            : 'Registration submitted successfully. Wait for your coordinator to verify your account before you log in.';

        return redirect('/login')->with('warning', $message);
    }

    public function logout(Request $request)
    {
        $student = Auth::guard('student')->user();
        if ($student) {
            $student->current_session_id = null;
            $student->save();
        }
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
            'password' => ['required', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
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

        return redirect()->route('student.settings')->with('success', 'Your password has been updated.');
    }
}
