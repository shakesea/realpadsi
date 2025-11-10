<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('Stok', function (Blueprint $table) {
            $table->string('ID_Barang', 10)->primary();
            $table->string('Nama', 100);
            $table->integer('Jumlah_Item');
            $table->decimal('Harga_Satuan', 10, 2);
            $table->timestamp('Created_At')->useCurrent();
            $table->timestamp('Updated_At')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('Menu', function (Blueprint $table) {
            $table->string('ID_Menu', 10)->primary();
            $table->string('Nama', 100);
            $table->decimal('Harga', 10, 2);
            $table->string('Kategori', 50);
            $table->text('Deskripsi')->nullable();
            $table->binary('Foto')->nullable();
            $table->timestamp('Created_At')->useCurrent();
            $table->timestamp('Updated_At')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('BahanPenyusun', function (Blueprint $table) {
            $table->string('ID_Penyusun', 10)->primary();
            $table->string('ID_Menu', 10);
            $table->string('ID_Barang', 10);
            $table->integer('Jumlah_Digunakan');
            $table->string('Kategori', 50);
            $table->timestamp('Created_At')->useCurrent();
            $table->timestamp('Updated_At')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('ID_Menu')->references('ID_Menu')->on('Menu')->onDelete('cascade');
            $table->foreign('ID_Barang')->references('ID_Barang')->on('Stok')->onDelete('cascade');
        });

        Schema::create('Manager', function (Blueprint $table) {
            $table->string('ID_Manager', 10)->primary();
            $table->string('Nama_Manager', 100);
            $table->string('Email', 100)->unique();
            $table->string('Password', 255);
            $table->timestamp('Created_At')->useCurrent();
            $table->timestamp('Updated_At')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('Pegawai', function (Blueprint $table) {
            $table->string('ID_Pegawai', 10)->primary();
            $table->string('Nama_Pegawai', 100);
            $table->string('Email', 100)->unique();
            $table->string('Password', 255);
            $table->timestamp('Created_At')->useCurrent();
            $table->timestamp('Updated_At')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('Member', function (Blueprint $table) {
            $table->string('ID_Member', 10)->primary();
            $table->string('Nama', 100);
            $table->string('Email', 100)->unique();
            $table->string('No_Telp', 15)->nullable();
            $table->integer('Poin')->default(0);
            $table->timestamp('Created_At')->useCurrent();
            $table->timestamp('Updated_At')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('TransaksiPenjualan', function (Blueprint $table) {
            $table->string('ID_Penjualan', 15)->primary();
            $table->string('ID_Pegawai', 10)->nullable();
            $table->string('ID_Manager', 10)->nullable();
            $table->string('ID_Member', 10)->nullable();
            $table->timestamp('Tgl_Penjualan')->useCurrent();
            $table->decimal('TotalHarga', 10, 2);
            $table->integer('Jumlah_Item');
            $table->string('Status', 20)->default('Selesai');
            $table->string('Metode_Pembayaran', 20);
            $table->integer('Poin_Digunakan')->default(0);
            $table->integer('Poin_Didapat')->default(0);
            $table->timestamp('Created_At')->useCurrent();
            $table->timestamp('Updated_At')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('ID_Pegawai')->references('ID_Pegawai')->on('Pegawai')->onDelete('set null');
            $table->foreign('ID_Manager')->references('ID_Manager')->on('Manager')->onDelete('set null');
            $table->foreign('ID_Member')->references('ID_Member')->on('Member')->onDelete('set null');
        });

        Schema::create('DetailPenjualan', function (Blueprint $table) {
            $table->string('ID_Detail', 15)->primary();
            $table->string('ID_Penjualan', 15);
            $table->string('ID_Menu', 10);
            $table->integer('Jumlah');
            $table->decimal('Harga', 10, 2);
            $table->timestamp('Created_At')->useCurrent();
            $table->timestamp('Updated_At')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('ID_Penjualan')->references('ID_Penjualan')->on('TransaksiPenjualan')->onDelete('cascade');
            $table->foreign('ID_Menu')->references('ID_Menu')->on('Menu')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('DetailPenjualan');
        Schema::dropIfExists('TransaksiPenjualan');
        Schema::dropIfExists('Member');
        Schema::dropIfExists('Pegawai');
        Schema::dropIfExists('Manager');
        Schema::dropIfExists('BahanPenyusun');
        Schema::dropIfExists('Menu');
        Schema::dropIfExists('Stok');
    }
};
