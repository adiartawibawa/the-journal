<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {

            $faker = \Faker\Factory::create('id_ID');
            $daftarKelas = [];
            $savedMapels = [];

            // 1. Ambil Role yang sudah dibuat oleh RolePermissionSeeder
            // Pastikan RolePermissionSeeder dijalankan sebelum MasterDataSeeder
            try {
                $superAdminRole = Role::findByName('super_admin', 'web');
                $adminRole      = Role::findByName('admin', 'web');
                $teacherRole    = Role::findByName('teacher', 'web');
                $studentRole    = Role::findByName('student', 'web');
            } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
                // Berikan pesan error yang informatif jika role belum ada
                throw new \Exception("Role tidak ditemukan. Pastikan Anda sudah menjalankan RolePermissionSeeder terlebih dahulu.");
            }

            // 2. Tahun Ajaran Aktif
            $ta = TahunAjaran::create([
                'nama' => '2025/2026',
                'semester' => '1',
                'tanggal_awal' => '2025-07-15',
                'tanggal_akhir' => '2025-12-20',
                'is_active' => true,
            ]);

            // 3. Master Data Kelas SMK
            $jurusans = [
                'RPL' => 'Rekayasa Perangkat Lunak',
                'TKJ' => 'Teknik Komputer dan Jaringan',
                'AP' => 'Administrasi Perhotelan',
                'KUL' => 'Tata Boga',
                'OTO' => 'Otomotif'
            ];
            $tingkat = [10, 11, 12];
            $paralel = ['1', '2'];

            foreach ($jurusans as $kodeJurusan => $namaJurusan) {
                foreach ($tingkat as $t) {
                    foreach ($paralel as $p) {
                        $daftarKelas[] = Kelas::create([
                            'kode' => "{$t}-{$kodeJurusan}-{$p}",
                            'nama' => "Kelas {$t} {$kodeJurusan} {$p}",
                            'tingkat' => $t,
                            'jurusan' => $kodeJurusan,
                            'kapasitas' => 36,
                        ]);
                    }
                }
            }

            // 4. Master Data Mapel
            $daftarMapel = [
                'Matematika',
                'Bahasa Indonesia',
                'Bahasa Inggris',
                'Pendidikan Agama',
                'PKn',
                'Sejarah',
                'PJOK',
                'Seni Budaya',
                'Simulasi Digital',
                'Informatika',
                'Produktif RPL',
                'Basis Data',
                'Pemrograman Web',
                'Pemrograman Mobile',
                'Jaringan Dasar',
                'Administrasi Server',
                'Keamanan Jaringan',
                'Akuntansi Dasar',
                'Manajemen Perhotelan',
                'Tata Hidang',
                'Pengolahan Makanan',
                'Teknik Kendaraan Ringan',
                'Kewirausahaan',
                'Fisika Terapan',
                'Kimia Terapan',
            ];

            foreach ($daftarMapel as $index => $namaMapel) {
                $savedMapels[] = Mapel::create([
                    'kode' => 'MP-' . str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                    'nama' => $namaMapel,
                    'kelompok' => chr(65 + ($index % 3)), // A, B, C
                ]);
            }

            // 5. SEED USERS UTAMA
            // Super Admin
            $super = User::create([
                'name' => 'Super Administrator',
                'email' => 'superadmin@dejournal.test',
                'password' => Hash::make('password'),
            ]);
            $super->assignRole($superAdminRole);

            // Admin
            $admin = User::create([
                'name' => 'Administrator',
                'email' => 'admin@dejournal.test',
                'password' => Hash::make('password'),
            ]);
            $admin->assignRole($adminRole);

            // 6. SEED 50 GURU
            foreach (range(1, 50) as $i) {
                $userGuru = User::create([
                    'name' => $faker->name(),
                    'email' => "guru{$i}@dejournal.test",
                    'password' => Hash::make('password'),
                ]);
                $userGuru->assignRole($teacherRole);

                $guru = Guru::create([
                    'user_id' => $userGuru->id,
                    'nuptk' => $faker->numerify('################'),
                    'status_kepegawaian' => $faker->randomElement(['PNS', 'PPPK', 'Guru Honor', 'Kontrak']),
                ]);

                // Wali Kelas (Maksimal sebanyak jumlah kelas yang ada)
                if (isset($daftarKelas[$i - 1])) {
                    $guru->waliKelas()->create([
                        'id' => Str::uuid(),
                        'kelas_id' => $daftarKelas[$i - 1]->id,
                        'tahun_ajaran_id' => $ta->id,
                        'is_active' => true,
                    ]);
                }

                // Relasi Guru Mengajar (Masing-masing guru mengajar 2 mapel acak)
                $mapelKeys = array_rand($savedMapels, 2);
                foreach ($mapelKeys as $key) {
                    DB::table('guru_mengajar')->insert([
                        'id' => Str::uuid(),
                        'guru_id' => $guru->id,
                        'mapel_id' => $savedMapels[$key]->id,
                        'kelas_id' => $daftarKelas[array_rand($daftarKelas)]->id,
                        'tahun_ajaran_id' => $ta->id,
                        'kkm' => 75,
                        'jam_per_minggu' => 4,
                        'is_active' => true,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }

            // 7. SEED 350 SISWA
            foreach (range(1, 350) as $j) {
                $userSiswa = User::create([
                    'name' => $faker->name(),
                    'email' => "siswa{$j}@dejournal.test",
                    'password' => Hash::make('password'),
                ]);
                $userSiswa->assignRole($studentRole);

                $siswa = Siswa::create([
                    'user_id' => $userSiswa->id,
                    'nisn' => $faker->numerify('00########'),
                    'is_active' => true,
                ]);

                // Masukkan Siswa ke Kelas Secara Acak
                $kelasAcak = $daftarKelas[array_rand($daftarKelas)];
                $siswa->kelasSiswa()->create([
                    'id' => Str::uuid(),
                    'kelas_id' => $kelasAcak->id,
                    'tahun_ajaran_id' => $ta->id,
                    'status' => 'aktif',
                    'tanggal_masuk' => Carbon::now()->subMonths(6),
                ]);
            }
        });
    }
}
