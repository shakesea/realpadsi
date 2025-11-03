<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $laporan = [
            ['pelanggan' => 'Tz Chaiyen', 'total' => 96800, 'kode' => 'S/251011/2/7', 'waktu' => '13:00:11'],
            ['pelanggan' => 'Cunyau', 'total' => 32800, 'kode' => 'S/251011/2/8', 'waktu' => '12:59:11'],
            ['pelanggan' => 'M6', 'total' => 189800, 'kode' => 'S/251011/2/9', 'waktu' => '12:34:11'],
            ['pelanggan' => 'Teta', 'total' => 96800, 'kode' => 'S/251011/2/10', 'waktu' => '11:45:00'],
            ['pelanggan' => 'Tami', 'total' => 96800, 'kode' => 'S/251011/2/11', 'waktu' => '11:30:30'],
        ];

        $start = $request->get('start') ?? \Carbon\Carbon::now()->subDays(4)->format('Y-m-d');
        $end   = $request->get('end') ?? \Carbon\Carbon::now()->format('Y-m-d');

        // nanti bisa ditambahkan filter database di sini

        return view('penjualan', compact('laporan', 'start', 'end'));
    }

}
