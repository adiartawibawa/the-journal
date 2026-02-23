<?php

namespace App\Observers;

use App\Models\AbsensiSiswa;
use App\Models\Jurnal;
use App\Models\Siswa;

class JurnalObserver
{
    public function saved(Jurnal $jurnal): void
    {
        // Hapus data absensi lama untuk jurnal ini (agar sinkron saat update)
        $jurnal->absensiSiswas()->delete();

        // 2. Ambil data dari kolom JSON 'absensi'
        // Format JSON: {"Nama Siswa": "Status", ...}
        $dataAbsensi = $jurnal->absensi ?? [];

        foreach ($dataAbsensi as $namaSiswa => $status) {
            // Cari ID siswa berdasarkan nama (atau bisa disesuaikan jika JSON menyimpan ID)
            $siswa = Siswa::whereHas('user', fn($q) => $q->where('name', $namaSiswa))->first();

            if ($siswa) {
                AbsensiSiswa::create([
                    'jurnal_id' => $jurnal->id,
                    'siswa_id'  => $siswa->id,
                    'status'    => $status,
                ]);
            }
        }
    }
}
