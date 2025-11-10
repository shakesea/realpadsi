<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\TransaksiPenjualanController;


use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/pegawai', [PegawaiController::class, 'index'])->name('pegawai.ndex');
Route::delete('/pegawai/{id}', [PegawaiController::class, 'destroy'])->name('pegawai.destroy');
Route::get('/pegawai/tambah', [PegawaiController::class, 'create'])->name('pegawai.create');
Route::post('/pegawai/tambah', [PegawaiController::class, 'store'])->name('pegawai.store');

Route::get('/kasir', [KasirController::class, 'index'])->name('kasir.index');
// JSON untuk modal Member di kasir
Route::get('/kasir/members/json', [MemberController::class, 'listForKasir'])
    ->name('kasir.members.json');

Route::post('/menu', [KasirController::class, 'store'])->name('menu.store');
Route::post('/transaksi', [TransaksiPenjualanController::class, 'store'])->name('transaksi.store');
Route::put('/menu/{id}', [KasirController::class, 'update'])->name('menu.update');
Route::delete('/menu/{id}', [KasirController::class, 'destroy'])->name('menu.destroy');

Route::prefix('stok')->group(function () {
    Route::get('/', [StokController::class, 'index'])->name('stok.index');
    Route::get('/tambah', [StokController::class, 'create'])->name('stok.create');
    Route::post('/', [StokController::class, 'store'])->name('stok.store');
    Route::get('/edit/{id}', [StokController::class, 'edit'])->name('stok.edit');
    Route::put('/{id}', [StokController::class, 'update'])->name('stok.update');
    Route::delete('/{id}', [StokController::class, 'destroy'])->name('stok.destroy');
});

Route::get('/penjualan', [LaporanController::class, 'index'])->name('penjualan.index');

Route::get('/kasir', [KasirController::class, 'index'])->name('kasir.index');
Route::post('/kasir/store', [KasirController::class, 'store'])->name('menu.store');




Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', function () {
    return view('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// Route::get('/penjualan', function () {
//     return view('penjualan');
// });

Route::get('/member', function () {
    return view('member');
});

Route::prefix('member')->group(function () {
    Route::get('/', [App\Http\Controllers\MemberController::class, 'index'])->name('member.index');
    Route::post('/', [App\Http\Controllers\MemberController::class, 'store'])->name('member.store');
    Route::delete('/{id}', [App\Http\Controllers\MemberController::class, 'destroy'])->name('member.destroy');
});

// ======================================================
// AUTH ROUTES
// ======================================================
Route::get('/', fn() => redirect('/login'));
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ======================================================
// PROTECTED ROUTES (HARUS LOGIN)
// ======================================================
Route::middleware('App\Http\Middleware\CheckLogin')->group(function () {

    // Semua role bisa lihat dashboard
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');

    // ==================================================
    // ROLE: MANAGER (akses semua halaman)
    // ==================================================
    Route::middleware('role:manager')->group(function () {
        Route::get('/pegawai', [PegawaiController::class, 'index'])->name('pegawai.index');
        Route::get('/pegawai/tambah', [PegawaiController::class, 'create'])->name('pegawai.create');
        Route::post('/pegawai/tambah', [PegawaiController::class, 'store'])->name('pegawai.store');
        Route::delete('/pegawai/{id}', [PegawaiController::class, 'destroy'])->name('pegawai.destroy');

        Route::get('/kasir', [KasirController::class, 'index'])->name('kasir.index');
        Route::post('/kasir/store', [KasirController::class, 'store'])->name('kasir.store');

        Route::get('/member', [MemberController::class, 'index'])->name('member.index');
        Route::post('/member', [MemberController::class, 'store'])->name('member.store');
        Route::delete('/member/{id}', [MemberController::class, 'destroy'])->name('member.destroy');

        Route::get('/stok', [StokController::class, 'index'])->name('stok.index');
        Route::get('/stok/tambah', [StokController::class, 'create'])->name('stok.create');
        Route::post('/stok', [StokController::class, 'store'])->name('stok.store');
        Route::get('/stok/edit/{id}', [StokController::class, 'edit'])->name('stok.edit');
        Route::put('/stok/{id}', [StokController::class, 'update'])->name('stok.update');
        Route::delete('/stok/{id}', [StokController::class, 'destroy'])->name('stok.destroy');

        Route::get('/penjualan', [LaporanController::class, 'index'])->name('penjualan.index');
    });

    // ==================================================
    // ROLE: PEGAWAI
    // ==================================================
    Route::middleware('role:pegawai')->group(function () {
        Route::get('/kasir', [KasirController::class, 'index'])->name('kasir.index');
        Route::post('/transaksi', [TransaksiPenjualanController::class, 'store'])->name('transaksi.store');

        Route::get('/member', [MemberController::class, 'index'])->name('member.index');

        Route::get('/stok', [StokController::class, 'index'])->name('stok.index');
        Route::get('/stok/edit/{id}', [StokController::class, 'edit'])->name('stok.edit');
        Route::put('/stok/{id}', [StokController::class, 'update'])->name('stok.update');
    });

    // ==================================================
    // ROLE: FINANCE
    // ==================================================
    Route::middleware('role:finance')->group(function () {
        Route::get('/penjualan', [LaporanController::class, 'index'])->name('penjualan.index');
    });
});