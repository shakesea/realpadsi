<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Manager;
use App\Models\Pegawai;
use App\Models\Finance;
use Illuminate\Support\Facades\Hash;
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
        $manager = Manager::where('Username', $request->username)->first();

        if ($manager) {
            // Check if password is bcrypt or plain text
            $passwordMatches = (strpos($manager->Password, '$2') === 0)
                ? Hash::check($request->password, $manager->Password)
                : $manager->Password === $request->password;

            if ($passwordMatches) {
                // Simpan data Manager ke session
                Session::put('user', [
                    'id' => $manager->ID_Manager,
                    'username' => $manager->Username,
                    'role' => 'manager',
                    'type' => 'manager'
                ]);


                return redirect('/dashboard')->with('success', 'Selamat datang kembali, ' . ucfirst(explode('.', $manager->Username)[0]) . '!');
            }
        }

        // Jika bukan Manager, cek apakah user adalah Pegawai
        $pegawai = Pegawai::where('Username', $request->username)->first();

        if ($pegawai) {
            // Check if password is bcrypt or plain text
            $passwordMatches = (strpos($pegawai->Password, '$2') === 0)
                ? Hash::check($request->password, $pegawai->Password)
                : $pegawai->Password === $request->password;

            if ($passwordMatches) {
                // Simpan data Pegawai ke session
                Session::put('user', [
                    'id' => $pegawai->ID_Pegawai,
                    'username' => $pegawai->Username,
                    'role' => 'pegawai',
                    'type' => 'pegawai'
                ]);

                return redirect('/dashboard')->with('success', 'Selamat datang kembali, ' . ucfirst(explode('.', $pegawai->Username)[0]) . '!');
            }
        }

        // Jika bukan Pegawai, cek apakah user adalah Finance
        $finance = Finance::where('Username', $request->username)->first();

        if ($finance) {
            // Check if password is bcrypt or plain text
            $passwordMatches = (strpos($finance->Password, '$2') === 0)
                ? Hash::check($request->password, $finance->Password)
                : $finance->Password === $request->password;

            if ($passwordMatches) {
                // Simpan data Finance ke session
                Session::put('user', [
                    'id' => $finance->ID_Finance,
                    'username' => $finance->Username,
                    'role' => 'finance',
                    'type' => 'finance'
                ]);

                return redirect('/dashboard')->with('success', 'Selamat datang kembali, ' . ucfirst(explode('.', $finance->Username)[0]) . '!');
            }
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
