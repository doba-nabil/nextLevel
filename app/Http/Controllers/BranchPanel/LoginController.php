<?php

namespace App\Http\Controllers\BranchPanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('branch.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $remember = false;

        if (Auth::guard('branch')->attempt($request->only('username', 'password'), $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route('branch.dashboard'));
        }

        throw ValidationException::withMessages([
            'username' => [trans('auth.failed')],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('branch')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('branch.login');
    }
}
