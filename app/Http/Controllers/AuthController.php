<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Manager;
use Illuminate\Support\Facades\Session;

class AuthController
{
    public function showLogin()
    {
        return view('login'); // pastikan ada file resources/views/login.blade.php
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $manager = Manager::where('Username', $request->username)
            ->where('Password', $request->password)
            ->first();

        if ($manager) {
            Session::put('manager', $manager);
            return redirect('/dashboard'); // ganti sesuai route dashboard kamu
        } else {
            return back()->with('error', 'Username atau password salah!');
        }
    }

    public function logout()
    {
        Session::forget('manager');
        return redirect('/login');
    }
}
