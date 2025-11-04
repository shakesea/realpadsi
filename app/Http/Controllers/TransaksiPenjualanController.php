<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\TransaksiPenjualan;
use Illuminate\Support\Facades\Session;

class TransaksiPenjualanController extends Controller
{
    // Menyimpan transaksi penjualan dari halaman kasir
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'total' => 'required|numeric',
            'metode' => 'nullable|string|max:50',
        ]);

        // Ambil data manager yang sedang login
        $manager = Session::get('manager');

        // Generate ID baru seperti TRX026
        $last = TransaksiPenjualan::orderBy('ID_Penjualan', 'desc')->first();
        if ($last) {
            preg_match('/(\d+)$/', $last->ID_Penjualan, $matches);
            $lastNumber = isset($matches[1]) ? intval($matches[1]) : 0;
        } else {
            $lastNumber = 0;
        }
        $newId = 'TRX' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

        // Simpan transaksi ke database
        $manager = Session::get('manager');

        $transaksi = TransaksiPenjualan::create([
            'ID_Penjualan' => $newId,
            'ID_Pegawai'   => null,
            'ID_Manager'   => $manager['ID_Manager'] ?? null,
            'Tgl_Penjualan'=> now(),
            'TotalHarga'   => $request->total,
            'Jumlah_Item'  => count($request->items),
            'Status'       => 'Selesai',
        ]);


        return response()->json([
            'status' => 'success',
            'message' => 'Transaksi berhasil disimpan',
            'id_transaksi' => $transaksi->ID_Penjualan,
            'manager' => $manager->Nama_Manager ?? null, // opsional, bisa tampilkan siapa yang login
        ]);
    }
}
