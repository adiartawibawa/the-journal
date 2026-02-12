<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Tahun Ajaran
        $ta = TahunAjaran::create([
            'nama' => '2025/2026',
            'semester' => '1',
            'tanggal_awal' => '2025-07-15',
            'tanggal_akhir' => '2025-12-20',
            'is_active' => true,
        ]);

        // 2. Mata Pelajaran
        $mtk = Mapel::create([
            'kode' => 'MTK-01',
            'nama' => 'Matematika',
            'kelompok' => 'A',
        ]);

        // 3. Kelas
        $kelas10a = Kelas::create([
            'kode' => 'X-A',
            'nama' => 'Kelas 10 Unggulan A',
            'tingkat' => 10,
            'jurusan' => 'IPA',
        ]);

        // SEED GURU
        $userGuru = User::create([
            'name' => 'Budi Utomo, S.Pd',
            'email' => 'budi@sekolah.sch.id',
            'password' => Hash::make('password'),
        ]);

        Guru::create([
            'user_id' => $userGuru->id,
            'nuptk' => '1234567890',
            'status_kepegawaian' => 'PNS',
        ]);

        // SEED SISWA
        $userSiswa = User::create([
            'name' => 'Andi Pratama',
            'email' => 'andi@siswa.sch.id',
            'password' => Hash::make('password'),
        ]);

        Siswa::create([
            'user_id' => $userSiswa->id,
            'nisn' => '00987654321',
            'is_active' => true,
        ]);

        // Penugasan
        $ta = TahunAjaran::where('is_active', true)->first();
        $guru = User::where('email', 'budi@sekolah.sch.id')->first();
        $siswa = User::where('email', 'andi@siswa.sch.id')->first();
        $kelas = Kelas::where('kode', 'X-A')->first();
        $mapel = Mapel::where('kode', 'MTK-01')->first();

        // 1. Set Guru sebagai Wali Kelas
        $guru->daftarWaliKelas()->attach($kelas->id, [
            'id' => Str::uuid(),
            'tahun_ajaran_id' => $ta->id,
            'is_active' => true,
        ]);

        // 2. Set Guru Mengajar Mata Pelajaran
        $guru->jadwalMengajar()->attach($mapel->id, [
            'id' => Str::uuid(),
            'tahun_ajaran_id' => $ta->id,
            'kelas_id' => $kelas->id,
            'kkm' => 75,
            'jam_per_minggu' => 4,
        ]);

        // 3. Daftarkan Siswa ke Kelas
        $siswa->riwayatKelas()->attach($kelas->id, [
            'id' => Str::uuid(),
            'tahun_ajaran_id' => $ta->id,
            'status' => 'aktif',
        ]);
    }
}
