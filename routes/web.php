<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\TransaksiPenjualanController;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| ROUTE PUBLIC (TANPA LOGIN)
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => redirect('/login'));

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| MIDTRANS SNAP (TANPA MIDDLEWARE)
|--------------------------------------------------------------------------
|
| HARUS DILETAKKAN DI SINI SUPAYA TIDAK KENA REDIRECT LOGIN!
| Jika Snap.js menerima HTML redirect login, akan muncul
| “Unexpected token < in JSON”.
|
*/
Route::post('/payment/snap', [PaymentController::class, 'createSnap'])
    ->name('payment.snap');

/*
|--------------------------------------------------------------------------
| ROUTE PROTECTED (HARUS LOGIN)
|--------------------------------------------------------------------------
*/
Route::middleware('App\Http\Middleware\CheckLogin')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD (Semua Role)
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // ✅ Route AJAX filter untuk dashboard
    Route::post('/dashboard/filter/ajax', [DashboardController::class, 'filterAjax'])
        ->name('dashboard.filter.ajax');

    /*
    |--------------------------------------------------------------------------
    | MANAGER
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:manager')->group(function () {

        // Pegawai
        Route::get('/pegawai', [PegawaiController::class, 'index'])->name('pegawai.index');
        Route::get('/pegawai/tambah', [PegawaiController::class, 'create'])->name('pegawai.create');
        Route::post('/pegawai/tambah', [PegawaiController::class, 'store'])->name('pegawai.store');
        Route::delete('/pegawai/{id}', [PegawaiController::class, 'destroy'])->name('pegawai.destroy');

        // Stok
        Route::get('/stok', [StokController::class, 'index'])->name('stok.index');
        Route::get('/stok/tambah', [StokController::class, 'create'])->name('stok.create');
        Route::post('/stok', [StokController::class, 'store'])->name('stok.store');
        Route::get('/stok/edit/{id}', [StokController::class, 'edit'])->name('stok.edit');
        Route::put('/stok/{id}', [StokController::class, 'update'])->name('stok.update');
        Route::delete('/stok/{id}', [StokController::class, 'destroy'])->name('stok.destroy');
        Route::get('/stok/export/pdf', [StokController::class, 'exportPDF'])->name('stok.export.pdf');

        // Member
        Route::get('/member', [MemberController::class, 'index'])->name('member.index');
        Route::post('/member', [MemberController::class, 'store'])->name('member.store');
        Route::delete('/member/{id}', [MemberController::class, 'destroy'])->name('member.destroy');

        // Penjualan
        Route::get('/penjualan', [LaporanController::class, 'index'])->name('penjualan.index');

        // Transaksi Penjualan
        Route::post('/transaksi/store', [TransaksiPenjualanController::class, 'store'])
            ->name('transaksi.store');
    });

    /*
    |--------------------------------------------------------------------------
    | PEGAWAI
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:pegawai')->group(function () {

        // Kasir
        Route::get('/kasir', [KasirController::class, 'index'])->name('kasir.index');
        Route::post('/transaksi/store', [TransaksiPenjualanController::class, 'store'])
            ->name('transaksi.store');

        // ✅ Tambahkan ini: route JSON untuk ambil data member (fix error Route not defined)
        Route::get('/kasir/members/json', [KasirController::class, 'getMembers'])
            ->name('kasir.members.json');

        // Member
        Route::get('/member', [MemberController::class, 'index'])
            ->name('member.index');

        // Stok (readonly edit)
        Route::get('/stok', [StokController::class, 'index'])->name('stok.index');
        Route::get('/stok/edit/{id}', [StokController::class, 'edit'])->name('stok.edit');
        Route::put('/stok/{id}', [StokController::class, 'update'])->name('stok.update');
        Route::get('/stok/export/pdf', [StokController::class, 'exportPDF'])->name('stok.export.pdf');
    });

    /*
    |--------------------------------------------------------------------------
    | FINANCE
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:finance')->group(function () {
        Route::get('/penjualan', [LaporanController::class, 'index'])
            ->name('penjualan.index');
    });

    /*
    |--------------------------------------------------------------------------
    | MENU (CRUD)
    |--------------------------------------------------------------------------
    */
    Route::prefix('menu')->group(function () {
        Route::get('/', [MenuController::class, 'index'])->name('menu.index');
        Route::post('/', [MenuController::class, 'store'])->name('menu.store');
        Route::put('/{id}', [MenuController::class, 'update'])->name('menu.update');
        Route::delete('/{id}', [MenuController::class, 'destroy'])->name('menu.destroy');

        // Bahan penyusun
        Route::get('/{id}/bahan', [MenuController::class, 'getBahanPenyusun'])
            ->name('menu.bahan');
    });

    /*
    |--------------------------------------------------------------------------
    | IMPORT PENJUALAN
    |--------------------------------------------------------------------------
    */
    Route::get('/penjualan/import', [LaporanController::class, 'showImport'])
        ->name('penjualan.import.show');

    Route::post('/penjualan/import', [LaporanController::class, 'import'])
        ->name('penjualan.import');

    Route::get('/penjualan/continue-import', [LaporanController::class, 'continueImport'])
        ->name('laporan.continue-import');
});
