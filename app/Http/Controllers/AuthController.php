<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Manager;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('login');
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
            // Simpan data login ke session
            Session::put('manager', [
                'ID_Manager' => $manager->ID_Manager,
                'Username'   => $manager->Username,
            ]);

            return redirect('/dashboard')->with('success', 'Selamat datang kembali, ' . ucfirst(explode('.', $manager->Username)[0]) . '!');
        }

        return back()->with('error', 'Username atau password salah!');
    }

    public function logout(Request $request)
    {
        if (Session::has('manager')) {
            $username = Session::get('manager.Username');
            Session::forget('manager');
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->with('success', 'Akun ' . $username . ' berhasil logout.');
        }

        return redirect('/login');
    }
}
