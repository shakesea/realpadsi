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

Route::get('/pegawai', [PegawaiController::class, 'index'])->name('pegawai.index');
Route::delete('/pegawai/{id}', [PegawaiController::class, 'destroy'])->name('pegawai.destroy');
Route::get('/pegawai/tambah', [PegawaiController::class, 'create'])->name('pegawai.create');
Route::post('/pegawai/tambah', [PegawaiController::class, 'store'])->name('pegawai.store');

Route::get('/kasir', [KasirController::class, 'index'])->name('kasir.index');
Route::post('/menu', [KasirController::class, 'store'])->name('menu.store');
Route::post('/transaksi', [TransaksiPenjualanController::class, 'store'])->name('transaksi.store');

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
    Route::get('/', [MemberController::class, 'index'])->name('member.index');
    Route::get('/tambah', [MemberController::class, 'create'])->name('member.create');
    Route::post('/', [MemberController::class, 'store'])->name('member.store');
    Route::get('/{id}/edit', [MemberController::class, 'edit'])->name('member.edit');
    Route::put('/{id}', [MemberController::class, 'update'])->name('member.update');
    Route::delete('/{id}', [MemberController::class, 'destroy'])->name('member.destroy');
});