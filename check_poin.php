<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CEK DATA POIN DI DATABASE ===\n\n";

// Cek 5 transaksi terakhir
$transaksi = \App\Models\TransaksiPenjualan::with('member')
    ->orderBy('Tgl_Penjualan', 'desc')
    ->take(5)
    ->get();

echo "📊 5 Transaksi Terakhir:\n";
echo str_repeat("-", 80) . "\n";

foreach ($transaksi as $trx) {
    echo sprintf(
        "ID: %-12s | Poin Digunakan: %3d | Poin Didapat: %3d | Total: Rp %s | Member: %s\n",
        $trx->ID_Penjualan,
        $trx->Poin_Digunakan ?? 0,
        $trx->Poin_Didapat ?? 0,
        number_format($trx->TotalHarga, 0, ',', '.'),
        $trx->ID_Member ?? '-'
    );

    if ($trx->member) {
        echo sprintf(
            "   └─ Member: %s (Poin Saat Ini: %d pts)\n",
            $trx->member->Nama,
            $trx->member->Poin ?? 0
        );
    }

    // Hitung potongan
    if ($trx->Poin_Digunakan > 0) {
        $potongan = $trx->Poin_Digunakan * 100;
        $totalBayar = max($trx->TotalHarga - $potongan, 0);
        echo sprintf(
            "   └─ Potongan: Rp %s (Total Bayar: Rp %s)\n",
            number_format($potongan, 0, ',', '.'),
            number_format($totalBayar, 0, ',', '.')
        );
    }

    echo "\n";
}

echo "\n=== CEK MEMBER & POIN ===\n";
echo str_repeat("-", 80) . "\n";

$members = \App\Models\Member::all();
foreach ($members as $member) {
    echo sprintf(
        "Member: %-20s | Poin: %5d pts\n",
        $member->Nama,
        $member->Poin ?? 0
    );
}

echo "\n✅ Selesai!\n";
