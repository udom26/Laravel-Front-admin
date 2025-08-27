<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $user = env('ADMIN_USER');
        $pass = env('ADMIN_PASS');

        if ($request->username === $user && $request->password === $pass) {
            session(['is_admin' => true]);
            return redirect()->route('admin.dashboard');
        }

        return back()->with('error', 'Invalid username or password');
    }

    public function logout()
    {
        session()->forget('is_admin');
        return redirect()->route('login')->with('success', 'Logged out successfully');
    }
}
