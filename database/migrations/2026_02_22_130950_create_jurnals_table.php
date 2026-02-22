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
        Schema::create('jurnals', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Relasi ke Data Master
            $table->foreignUuid('tahun_ajaran_id')->constrained('tahun_ajarans')->cascadeOnDelete();
            $table->foreignUuid('guru_id')->constrained('gurus')->cascadeOnDelete();
            $table->foreignUuid('mapel_id')->constrained('mapels')->cascadeOnDelete();
            $table->foreignUuid('kelas_id')->constrained('kelas')->cascadeOnDelete();

            // Inti Laporan Jurnal
            $table->date('tanggal');
            $table->json('jam_ke'); // Disimpan sebagai array JSON (contoh: [1, 2])
            $table->string('materi');
            $table->longText('kegiatan');

            // Data Pendukung (JSON & File)
            $table->json('absensi')->nullable(); // [{"siswa_id": "...", "status": "A/I/S"}]
            $table->string('keterangan')->nullable(); // Catatan tambahan atau alasan revisi

            // Status Alur Kerja (Workflow)
            $table->string('status_verifikasi')->default('pending'); // pending, disetujui, ditolak

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurnals');
    }
};
