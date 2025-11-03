<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        // Validasi manual dulu
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $username = $request->username;
        $password = $request->password;

        // DEBUG: Cek nilai
        \Log::info("Login attempt - Username: $username, Password: $password");

        // Credentials hardcoded untuk testing
        $defaultUsername = 'manager01';
        $defaultPassword = 'admin123';

        if ($username === $defaultUsername && $password === $defaultPassword) {
            // Login sukses
            session(['user' => [
                'id' => 1,
                'username' => 'manager01',
                'name' => 'Manager'
            ]]);
            $request->session()->regenerate();
            
            return redirect()->intended('/dashboard');
        }

        // Login gagal - return dengan error
        return back()->withErrors([
            'error' => 'Username atau password tidak sesuai.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}