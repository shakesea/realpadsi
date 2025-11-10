<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed Manager
        DB::table('Manager')->insert([
            'ID_Manager' => 'MGR001',
            'Nama_Manager' => 'John Doe',
            'Email' => 'manager@example.com',
            'Password' => Hash::make('password'),
            'Created_At' => now(),
            'Updated_At' => now()
        ]);

        // Seed Pegawai
        DB::table('Pegawai')->insert([
            'ID_Pegawai' => 'PEG001',
            'Nama_Pegawai' => 'Jane Smith',
            'Email' => 'kasir@example.com',
            'Password' => Hash::make('password'),
            'Created_At' => now(),
            'Updated_At' => now()
        ]);

        // Seed Member
        DB::table('Member')->insert([
            'ID_Member' => 'MEM001',
            'Nama' => 'Alice Johnson',
            'Email' => 'alice@example.com',
            'No_Telp' => '081234567890',
            'Poin' => 100,
            'Created_At' => now(),
            'Updated_At' => now()
        ]);

        // Seed Stok
        $stokItems = [
            ['ID_Barang' => 'BRG001', 'Nama' => 'Coffee Beans Arabica', 'Jumlah_Item' => 100, 'Harga_Satuan' => 50000],
            ['ID_Barang' => 'BRG002', 'Nama' => 'Fresh Milk', 'Jumlah_Item' => 50, 'Harga_Satuan' => 15000],
            ['ID_Barang' => 'BRG003', 'Nama' => 'Chocolate Powder', 'Jumlah_Item' => 75, 'Harga_Satuan' => 25000],
        ];

        foreach ($stokItems as $item) {
            DB::table('Stok')->insert(array_merge($item, [
                'Created_At' => now(),
                'Updated_At' => now()
            ]));
        }

        // Seed Menu
        $menuItems = [
            [
                'ID_Menu' => 'MENU001',
                'Nama' => 'Cafe Latte',
                'Harga' => 25000,
                'Kategori' => 'Coffee',
                'Deskripsi' => 'Espresso with steamed milk'
            ],
            [
                'ID_Menu' => 'MENU002',
                'Nama' => 'Hot Chocolate',
                'Harga' => 20000,
                'Kategori' => 'Non-Coffee',
                'Deskripsi' => 'Rich chocolate with steamed milk'
            ]
        ];

        foreach ($menuItems as $item) {
            DB::table('Menu')->insert(array_merge($item, [
                'Created_At' => now(),
                'Updated_At' => now()
            ]));
        }

        // Seed Bahan Penyusun
        $bahanItems = [
            [
                'ID_Penyusun' => 'BP001',
                'ID_Menu' => 'MENU001',
                'ID_Barang' => 'BRG001',
                'Jumlah_Digunakan' => 1,
                'Kategori' => 'Coffee'
            ],
            [
                'ID_Penyusun' => 'BP002',
                'ID_Menu' => 'MENU001',
                'ID_Barang' => 'BRG002',
                'Jumlah_Digunakan' => 1,
                'Kategori' => 'Coffee'
            ]
        ];

        foreach ($bahanItems as $item) {
            DB::table('BahanPenyusun')->insert(array_merge($item, [
                'Created_At' => now(),
                'Updated_At' => now()
            ]));
        }

        // Seed Transaksi Penjualan
        $trxItems = [
            [
                'ID_Penjualan' => 'TRX001',
                'ID_Pegawai' => 'PEG001',
                'ID_Member' => 'MEM001',
                'Tgl_Penjualan' => Carbon::now()->subDays(1),
                'TotalHarga' => 45000,
                'Jumlah_Item' => 2,
                'Status' => 'Selesai',
                'Metode_Pembayaran' => 'Tunai',
                'Poin_Digunakan' => 0,
                'Poin_Didapat' => 4
            ],
            [
                'ID_Penjualan' => 'TRX002',
                'ID_Manager' => 'MGR001',
                'Tgl_Penjualan' => Carbon::now(),
                'TotalHarga' => 25000,
                'Jumlah_Item' => 1,
                'Status' => 'Selesai',
                'Metode_Pembayaran' => 'QRIS',
                'Poin_Digunakan' => 0,
                'Poin_Didapat' => 0
            ]
        ];

        foreach ($trxItems as $item) {
            DB::table('TransaksiPenjualan')->insert(array_merge($item, [
                'Created_At' => now(),
                'Updated_At' => now()
            ]));
        }

        // Seed Detail Penjualan
        $detailItems = [
            [
                'ID_Detail' => 'DTL001',
                'ID_Penjualan' => 'TRX001',
                'ID_Menu' => 'MENU001',
                'Jumlah' => 1,
                'Harga' => 25000
            ],
            [
                'ID_Detail' => 'DTL002',
                'ID_Penjualan' => 'TRX001',
                'ID_Menu' => 'MENU002',
                'Jumlah' => 1,
                'Harga' => 20000
            ],
            [
                'ID_Detail' => 'DTL003',
                'ID_Penjualan' => 'TRX002',
                'ID_Menu' => 'MENU001',
                'Jumlah' => 1,
                'Harga' => 25000
            ]
        ];

        foreach ($detailItems as $item) {
            DB::table('DetailPenjualan')->insert(array_merge($item, [
                'Created_At' => now(),
                'Updated_At' => now()
            ]));
        }
    }
}
