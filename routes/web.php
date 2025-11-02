<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', function () {
    return view('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/pegawai', function () {
    return view('pegawai');
});

Route::get('/kasir', function () {
    return view('kasir');
});

Route::get('/stok', function () {
    return view('stok');
});

Route::get('/penjualan', function () {
    return view('penjualan');
});

Route::get('/member', function () {
    return view('member');
});