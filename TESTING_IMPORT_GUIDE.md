# File Testing Import Penjualan

## Daftar File Testing

### 1. **testing_import_penjualan.csv**

File CSV siap pakai untuk testing import dengan 12 record transaksi yang berbeda

## Struktur Data

Kolom yang diperlukan:

```
ID_Penjualan       - ID unik transaksi (format: TRX001, TRX002, dst)
Tgl_Penjualan      - Tanggal dan waktu transaksi (format: MM/DD/YYYY HH:MM)
ID_Pegawai         - ID Pegawai (opsional, jika kosong gunakan Manager)
ID_Manager         - ID Manager (opsional, jika kosong gunakan Pegawai)
ID_Member          - ID Member (opsional)
Metode_Pembayaran  - Metode pembayaran (Tunai, Kartu Kredit, QRIS, E-Wallet)
TotalHarga         - Total harga transaksi
Jumlah_Item        - Jumlah item yang dibeli
Status             - Status transaksi (Selesai, Pending, dll)
Poin_Digunakan     - Poin yang digunakan dari member
Poin_Didapat       - Poin yang didapat dari transaksi
ID_Menu            - ID Menu (harus sudah ada di database)
Quantity           - Jumlah quantity menu
Subtotal           - Subtotal harga menu
Nama_Menu          - Nama menu (untuk referensi)
Kategori_Menu      - Kategori menu (untuk referensi)
Harga_Menu         - Harga satuan menu (untuk referensi)
```

## Testing Scenarios

File yang disediakan berisi test case untuk:

### ✅ Normal Cases

-   Transaksi dengan Pegawai (ID_Pegawai diisi)
-   Transaksi dengan Manager (ID_Manager diisi)
-   Transaksi dengan Member (ID_Member diisi)
-   Transaksi dengan kombinasi Pegawai + Manager + Member

### ✅ Edge Cases

-   Transaksi tanpa ID_Pegawai (hanya Manager)
-   Transaksi tanpa ID_Manager (hanya Pegawai)
-   Transaksi tanpa Member
-   Berbagai metode pembayaran
-   Poin digunakan dan poin yang didapat

## Cara Testing

1. Download file **testing_import_penjualan.csv**
2. Pastikan Master Data sudah ada:
    - Menu: MN001, MN002, MN003, MN004, MN005
    - Pegawai: EMP001, EMP002, EMP003 (atau akan dibuat otomatis)
    - Manager: MG001, MG002, MG003 (atau akan dibuat otomatis)
    - Member: MBR001, MBR002, MBR003, MBR004, MBR005 (atau akan dibuat otomatis)
3. Jalankan import melalui halaman import penjualan
4. Lihat hasilnya di halaman laporan penjualan

## Notes

-   Jika data Master (Pegawai, Manager, Member) belum ada, sistem akan membuatnya otomatis
-   Default password untuk Pegawai/Manager yang dibuat otomatis: `password123`
-   Format tanggal harus MM/DD/YYYY HH:MM
-   Semua field dalam format CSV standar (comma-separated)
