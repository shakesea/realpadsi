<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST BAHAN PENYUSUN MENU ===\n\n";

// Pilih menu pertama yang ada
$menu = \App\Models\Menu::with('bahanPenyusun.stok')->first();

if (!$menu) {
    echo "❌ Tidak ada menu di database\n";
    exit;
}

echo "📋 Menu: {$menu->Nama} (ID: {$menu->ID_Menu})\n";
echo "💰 Harga: Rp " . number_format($menu->Harga, 0, ',', '.') . "\n";
echo "📂 Kategori: {$menu->Kategori}\n";
echo str_repeat("-", 80) . "\n";

$bahanList = \App\Models\BahanPenyusun::where('ID_Menu', $menu->ID_Menu)
    ->join('Stok', 'Bahan_Penyusun.ID_Barang', '=', 'Stok.ID_Barang')
    ->select('Bahan_Penyusun.*', 'Stok.Nama as Nama_Barang')
    ->get();

echo "🧪 Bahan Penyusun ({$bahanList->count()} items):\n";
echo str_repeat("-", 80) . "\n";

if ($bahanList->isEmpty()) {
    echo "⚠️ Menu ini BELUM memiliki bahan penyusun!\n";
} else {
    foreach ($bahanList as $bahan) {
        echo sprintf(
            "  • %s: %s unit (ID Barang: %s)\n",
            $bahan->ID_Penyusun,
            $bahan->Jumlah_Digunakan,
            $bahan->ID_Barang
        );
        echo sprintf(
            "    Nama Bahan: %s\n",
            $bahan->Nama_Barang
        );
    }
}

echo "\n=== TEST SIMULASI UPDATE ===\n";
echo str_repeat("-", 80) . "\n";

// Simulasi data yang dikirim dari form edit
$testBahan = ['BRG001', 'BRG002'];
$testJumlah = [5, 3];

echo "Data yang akan dikirim:\n";
echo "  bahan[] = ['" . implode("', '", $testBahan) . "']\n";
echo "  jumlah_digunakan[] = [" . implode(", ", $testJumlah) . "]\n\n";

// Validasi
$validBahan = collect($testBahan)
    ->filter(function ($bahan, $index) use ($testJumlah) {
        $jumlah = $testJumlah[$index] ?? 0;
        return !empty($bahan) && $jumlah > 0;
    });

echo "✅ Bahan valid: " . $validBahan->count() . " items\n";

if ($validBahan->isEmpty()) {
    echo "❌ GAGAL: Tidak ada bahan yang valid!\n";
} else {
    echo "✅ LOLOS: Validasi berhasil\n";
}

echo "\n✅ Test selesai!\n";
