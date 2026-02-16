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
        Schema::create('tahun_ajarans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama', 9); // 2025/2026
            $table->enum('semester', ['1', '2']);
            $table->date('tanggal_awal');
            $table->date('tanggal_akhir');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('gurus', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nuptk')->unique()->nullable();
            $table->string('status_kepegawaian')->nullable(); // PNS, PPPK, Honor
            $table->date('tanggal_masuk')->nullable();
            $table->string('bidang_studi')->nullable();
            $table->enum('golongan', ['I/a', 'I/b', 'I/c', 'I/d', 'II/a', 'II/b', 'II/c', 'II/d', 'III/a', 'III/b', 'III/c', 'III/d', 'IV/a', 'IV/b', 'IV/c', 'IV/d', 'IV/e'])->nullable();
            $table->string('pendidikan_terakhir')->nullable();
            $table->timestamps();
        });

        Schema::create('siswas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nisn')->unique()->nullable();
            $table->string('tanggal_lahir')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->string('nama_ayah')->nullable();
            $table->string('nama_ibu')->nullable();
            $table->string('pekerjaan_orang_tua')->nullable();
            $table->string('alamat_orang_tua')->nullable();
            $table->string('no_telp_orang_tua')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('mapels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 20)->unique();
            $table->string('nama');
            $table->enum('kelompok', ['A', 'B', 'C'])->default('A'); // A: Wajib, B: Jurusan, C: Peminatan
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('kelas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 50)->unique();
            $table->string('nama');
            $table->smallInteger('tingkat');
            $table->string('jurusan')->nullable();
            $table->integer('kapasitas')->default(40);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('guru_mengajar', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tahun_ajaran_id')->constrained('tahun_ajarans');
            $table->foreignUuid('kelas_id')->constrained('kelas');
            $table->foreignUuid('guru_id')->constrained('gurus')->onDelete('cascade');
            $table->foreignUuid('mapel_id')->constrained('mapels');
            $table->integer('kkm')->default(75);
            $table->integer('jam_per_minggu')->default(2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('wali_kelas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tahun_ajaran_id')->constrained('tahun_ajarans');
            $table->foreignUuid('kelas_id')->constrained('kelas');
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('kelas_siswa', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tahun_ajaran_id')->constrained('tahun_ajarans');
            $table->foreignUuid('kelas_id')->constrained('kelas');
            $table->foreignUuid('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->enum('status', ['aktif', 'pindah', 'lulus', 'dropout'])->default('aktif');
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas_siswa');
        Schema::dropIfExists('wali_kelas');
        Schema::dropIfExists('guru_mengajar');
        Schema::dropIfExists('kelas');
        Schema::dropIfExists('mapels');
        Schema::dropIfExists('siswas');
        Schema::dropIfExists('gurus');
        Schema::dropIfExists('tahun_ajarans');
    }
};
