<?php

namespace App\Observers;

use App\Models\KelasSiswa;
use App\Models\RiwayatStatusSiswa;
use Illuminate\Support\Str;

class KelasSiswaObserver
{
    /**
     * Handle the KelasSiswa "created" event.
     */
    public function created(KelasSiswa $kelasSiswa): void
    {
        // Untuk data baru, status_lama kita anggap 'null' atau 'baru'
        $this->catatRiwayat($kelasSiswa, 'baru', $kelasSiswa->status, "Pendaftaran awal siswa di kelas: " . ($kelasSiswa->kelas?->nama ?? 'Unit Baru'));
    }

    /**
     * Handle the KelasSiswa "updated" event.
     */
    public function updated(KelasSiswa $kelasSiswa): void
    {
        // Hanya catat jika kolom 'status' atau 'hasil_akhir' berubah
        if ($kelasSiswa->isDirty(['status', 'hasil_akhir'])) {

            $statusLama = $kelasSiswa->getOriginal('status');
            $statusBaru = $kelasSiswa->status;
            $hasilAkhir = $kelasSiswa->hasil_akhir ?? 'proses';

            $alasan = "Perubahan status ke {$statusBaru} (Hasil: {$hasilAkhir}).";

            // Jika perubahan berasal dari method manual (Lanjut Semester/Naik Kelas),
            $this->catatRiwayat($kelasSiswa, $statusLama, $statusBaru, $alasan);
        }
    }

    /**
     * Handle the KelasSiswa "deleted" event.
     */
    public function deleted(KelasSiswa $kelasSiswa): void
    {
        $this->catatRiwayat($kelasSiswa, $kelasSiswa->status, 'terhapus', "Record keanggotaan kelas dihapus dari sistem.");
    }

    /**
     * Handle the KelasSiswa "restored" event.
     */
    public function restored(KelasSiswa $kelasSiswa): void
    {
        //
    }

    /**
     * Handle the KelasSiswa "force deleted" event.
     */
    public function forceDeleted(KelasSiswa $kelasSiswa): void
    {
        //
    }

    /**
     * Helper internal untuk mencatat ke tabel riwayat
     */
    private function catatRiwayat(KelasSiswa $kelasSiswa, string $statusLama, string $statusBaru, string $alasan): void
    {
        RiwayatStatusSiswa::create([
            'id' => (string) Str::uuid(),
            'siswa_id' => $kelasSiswa->siswa_id,
            'status_lama' => $statusLama,
            'status_baru' => $statusBaru,
            'alasan' => $alasan,
            'tanggal_perubahan' => now()->format('Y-m-d'), // Kolom tipe date
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
