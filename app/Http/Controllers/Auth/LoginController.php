<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {

            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->branch_id) {
                $branch = \App\Models\Branch::find($user->branch_id);
                if ($branch && !$branch->is_active) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return back()->withErrors([
                        'email' => 'This branch is currently disabled by the owner.',
                    ]);
                }
            }

            $user->session_id = session()->getId();
            $user->save();

            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid email or password.',
        ]);
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        if (User::count() >= 4) {
            return back()->withErrors(['error' => 'Registration is closed. Maximum user limit (4) reached.']);
        }

        $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|unique:users,email',
            'password'         => 'required|string|min:6|confirmed',
            'role'             => 'required|in:owner,staff',
            'branch'           => 'nullable|string|max:255',
            'security_contact' => 'required|string|max:255',
        ]);

        $branchName = $request->role === 'owner'
            ? 'Moroboro Branch'
            : $request->branch;
        $branchObj = \App\Models\Branch::where('branch_name', $branchName)->first();

        User::create([
            'name'             => $request->name,
            'email'            => $request->email,
            'password'         => $request->password,
            'plain_password'   => $request->password,
            'role'             => $request->role,
            'branch'           => $branchName,
            'branch_id'        => $branchObj ? $branchObj->id : null,
            'security_contact' => $request->security_contact,
        ]);

        return redirect()
            ->route('login')
            ->with('success', 'Account registered successfully.');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $user->session_id = null;
            $user->save();
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}