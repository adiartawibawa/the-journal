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
        // Mengubah Nama Kolom (Rename)
        Schema::table('kelas_siswa', function (Blueprint $table) {
            $table->renameColumn('tanggal_mulai', 'tanggal_masuk');
            $table->renameColumn('tanggal_selesai', 'tanggal_keluar');
        });

        // Mengubah Enum, Menambah Kolom Baru, dan Index
        Schema::table('kelas_siswa', function (Blueprint $table) {
            // Mengubah Enum Status
            $table->enum('status', ['aktif', 'mutasi', 'keluar'])
                ->default('aktif')
                ->change();

            // Menambah kolom hasil_akhir setelah status
            $table->enum('hasil_akhir', ['naik_kelas', 'tinggal_kelas', 'lulus', 'diskualifikasi'])
                ->nullable()
                ->after('status');

            // Menambah catatan_internal setelah tanggal_keluar (yang sudah berhasil di-rename)
            $table->text('catatan_internal')
                ->nullable()
                ->after('tanggal_keluar');

            // Menambahkan Index
            $table->index(['kelas_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
