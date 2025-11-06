<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Manager;
use App\Models\Pegawai;
use App\Models\Finance;

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

        // Cek apakah user adalah Manager
        $manager = Manager::where('Username', $request->username)
            ->where('Password', $request->password)
            ->first();

        if ($manager) {
            // Simpan data Manager ke session
            Session::put('user', [
                'id' => $manager->ID_Manager,
                'username' => $manager->Username,
                'role' => 'manager',
                'type' => 'manager'
            ]);
            

            return redirect('/dashboard')->with('success', 'Selamat datang kembali, ' . ucfirst(explode('.', $manager->Username)[0]) . '!');
        }

        // Jika bukan Manager, cek apakah user adalah Pegawai
        $pegawai = Pegawai::where('Username', $request->username)
            ->where('Password', $request->password)
            ->first();

        if ($pegawai) {
            // Simpan data Pegawai ke session
            Session::put('user', [
                'id' => $pegawai->ID_Pegawai,
                'username' => $pegawai->Username,
                'role' => 'pegawai', // Bisa digunakan untuk cek role pegawai
                'type' => 'pegawai'
            ]);

            return redirect('/dashboard')->with('success', 'Selamat datang kembali, ' . ucfirst(explode('.', $pegawai->Username)[0]) . '!');
        }

        $finance = Finance::where('Username', $request->username)
            ->where('Password', $request->password)
            ->first();

        if ($finance) {
            // Simpan data Finance ke session
            Session::put('user', [
                'id' => $finance->ID_Finance,
                'username' => $finance->Username,
                'role' => 'finance',
                'type' => 'finance'
            ]);

            return redirect('/dashboard')->with('success', 'Selamat datang kembali, ' . ucfirst(explode('.', $finance->Username)[0]) . '!');
        }

        return back()->with('error', 'Username atau password salah!');
    }

    public function logout(Request $request)
    {
        if (Session::has('user')) {
            $username = Session::get('user.username');
            Session::forget('user');
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->with('success', 'Akun ' . $username . ' berhasil logout.');
        }

        return redirect('/login');
    }
}