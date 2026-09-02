<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetCode;

class ForgotPasswordController extends Controller
{
    public function showEmailForm()
    {
        return view('auth.forgot-email');
    }

    public function sendCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $code = random_int(100000, 999999);

            session([
                'reset_user_id' => $user->id,
                'reset_code'    => $code,
                'reset_expiry'  => now()->addMinutes(10),
            ]);

            try {
                Mail::to($user->email)->send(new PasswordResetCode($code, $user->name));
            } catch (\Exception $e) {
                return back()->withErrors(['email' => 'Unable to send email. Check your mail configuration in .env.']);
            }

            return redirect()->route('forgot.code')->with('email', $user->email);
        }

        return back()->with('message', 'If that email is registered, a verification code has been sent.');
    }

    public function showCodeForm()
    {
        if (!session('reset_user_id') || !session('reset_code') || !session('reset_expiry')) {
            return redirect()->route('forgot.email')
                ->withErrors(['email' => 'Please start the password reset process.']);
        }

        return view('auth.forgot-code');
    }

    public function verifyCode(Request $request)
    {
        $request->validate(['code' => 'required|numeric|digits:6']);

        if (!session('reset_user_id') || !session('reset_code') || !session('reset_expiry')) {
            return redirect()->route('forgot.email')
                ->withErrors(['email' => 'Session expired. Please start again.']);
        }

        if (now()->greaterThan(session('reset_expiry'))) {
            return redirect()->route('forgot.email')
                ->withErrors(['email' => 'Code has expired. Please request a new one.']);
        }

        if ((int) $request->code !== (int) session('reset_code')) {
            return back()->withErrors(['code' => 'Invalid code. Please try again.']);
        }

        session(['reset_verified' => true]);

        return redirect()->route('forgot.reset');
    }

    public function showResetForm()
    {
        if (!session('reset_verified') || !session('reset_user_id')) {
            return redirect()->route('forgot.email')
                ->withErrors(['email' => 'Please start the password reset process.']);
        }

        return view('auth.forgot-reset');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (!session('reset_verified') || !session('reset_user_id')) {
            return redirect()->route('forgot.email')
                ->withErrors(['email' => 'Session expired. Please start again.']);
        }

        $user = User::find(session('reset_user_id'));
        if (!$user) {
            return redirect()->route('forgot.email')
                ->withErrors(['email' => 'User not found.']);
        }

        $user->password = Hash::make($request->password);
        $user->session_id = null;
        $user->save();

        session()->forget(['reset_user_id', 'reset_code', 'reset_expiry', 'reset_verified']);

        return redirect()->route('login')
            ->with('success', 'Password has been reset successfully. All sessions have been terminated. Please log in.');
    }
}
