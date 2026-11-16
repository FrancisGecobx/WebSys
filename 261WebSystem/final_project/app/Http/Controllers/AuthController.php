<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // 1. Render the Login Form View
    public function showLogin()
    {
        return view('login');
    }

    // 2. Handle Login Submission for both Student & Teacher
    public function login(Request $request)
    {
        $userType = $request->input('user_type', 'teacher');

        if ($userType === 'student') {
            $request->validate([
                'student_number' => 'required|string',
                'password'       => 'required|string',
            ]);

            $student = DB::table('students')
                ->where('student_number', $request->student_number)
                ->orWhere('email', $request->student_number)
                ->first();

            if ($student && Hash::check($request->password, $student->password_hash)) {
                session([
                    'student_id'     => $student->student_id,
                    'student_name'   => $student->name,
                    'student_number' => $student->student_number,
                    'role'           => 'student'
                ]);

                return redirect('/student/dashboard');
            }

            return redirect()->back()->with('error', 'Invalid Student ID / Email or Password.');

        } else {
            $request->validate([
                'email'    => 'required|email',
                'password' => 'required|string',
            ]);

            $staff = DB::table('staff_admin')
                ->where('email', $request->email)
                ->where('is_active', true)
                ->first();

            if ($staff && Hash::check($request->password, $staff->password_hash)) {
                session([
                    'staff_admin_id' => $staff->staff_admin_id,
                    'staff_name'     => $staff->name,
                    'staff_role'     => $staff->role,
                    'role'           => 'staff'
                ]);

                return redirect('/dashboard');
            }

            return redirect()->back()->with('error', 'Invalid Email or Password.');
        }
    }

    // 3. Handle Logout
    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect('/login');
    }

    public function showForgotPassword()
    {
        return view('forgot_password');
    }

    public function sendTemporaryPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Check students table first, then staff_admin table
        $student = DB::table('students')->where('email', $request->email)->first();
        $admin   = !$student ? DB::table('staff_admin')->where('email', $request->email)->first() : null;

        if (!$student && !$admin) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['email' => 'No student or staff account is linked to that email address.']);
        }

        // Generate a clean 8-character temporary password
        $tempPassword = Str::upper(Str::random(4)) . rand(1000, 9999);
        $hashedPassword = Hash::make($tempPassword);

        $recipientName = $student ? $student->name : $admin->name;
        $loginHint     = $student ? "Student ID ({$student->student_number}) or Email" : "Admin Email";

        try {
            $emailBody = "Hello {$recipientName},\n\n"
                . "We received a password reset request for your Aldersgate College Prefect of Discipline Portal account.\n\n"
                . "Temporary Password: {$tempPassword}\n"
                . "Login Using: {$loginHint}\n\n"
                . "Please log in and change your password immediately under 'My Profile'.\n\n"
                . "— Office of the Prefect of Discipline\n"
                . "Aldersgate College Inc.";

            Mail::raw($emailBody, function ($mail) use ($request) {
                $mail->to($request->email)
                     ->subject('[POD Portal] Temporary Password Reset');
            });

            // Only update the database AFTER Gmail successfully sends the email
            if ($student) {
                DB::table('students')
                    ->where('student_id', $student->student_id)
                    ->update(['password_hash' => $hashedPassword]);
            } else {
                DB::table('staff_admin')
                    ->where('staff_admin_id', $admin->staff_admin_id)
                    ->update(['password_hash' => $hashedPassword]);
            }

            return redirect('/login')->with('success', 'A temporary password has been sent to your Gmail address!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['email' => 'Failed to send reset email. Please verify your internet connection or contact the POD Office.']);
        }
    }
}