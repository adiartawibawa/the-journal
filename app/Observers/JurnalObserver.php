<?php

namespace App\Observers;

use App\Models\AbsensiSiswa;
use App\Models\Jurnal;
use App\Models\Siswa;

class JurnalObserver
{
    public function saved(Jurnal $jurnal): void
    {
        // Hapus data absensi lama untuk jurnal ini (agar tidak duplikat saat edit)
        $jurnal->absensiSiswas()->delete();

        // Jika ada data di kolom JSON 'absensi'
        if ($jurnal->absensi && is_array($jurnal->absensi)) {
            foreach ($jurnal->absensi as $namaSiswa => $info) {
                // Cari ID Siswa berdasarkan Nama (melalui relasi User)
                $siswa = Siswa::whereHas('user', function ($q) use ($namaSiswa) {
                    $q->where('name', $namaSiswa);
                })->first();

                if ($siswa) {
                    // Ekstrak Status dan Keterangan dari string "Sakit (Izin Lomba)"
                    // Regex untuk memisahkan teks di luar kurung dan di dalam kurung
                    preg_match('/^(.*?)(?:\s\((.*?)\))?$/', $info, $matches);

                    $status = trim($matches[1] ?? $info);
                    $keterangan = $matches[2] ?? null;

                    AbsensiSiswa::create([
                        'jurnal_id' => $jurnal->id,
                        'siswa_id'  => $siswa->id,
                        'status'    => $status,
                        'keterangan' => $keterangan,
                    ]);
                }
            }
        }
    }
}
