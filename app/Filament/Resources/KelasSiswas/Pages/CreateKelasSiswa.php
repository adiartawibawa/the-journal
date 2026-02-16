<?php

namespace App\Filament\Resources\KelasSiswas\Pages;

use App\Filament\Resources\KelasSiswas\KelasSiswaResource;
use App\Models\KelasSiswa;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateKelasSiswa extends CreateRecord
{
    protected static string $resource = KelasSiswaResource::class;

    protected function afterCreate(): void
    {
        $record = $this->record;

        // Cek kapasitas kelas
        $kelas = $record->kelas;
        $totalSiswa = KelasSiswa::where('kelas_id', $kelas->id)
            ->where('tahun_ajaran_id', $record->tahun_ajaran_id)
            ->where('status', 'aktif')
            ->count();

        if ($totalSiswa > $kelas->kapasitas) {
            Notification::make()
                ->warning()
                ->title('Perhatian: Kapasitas Kelas Melebihi Batas')
                ->body("Kelas {$kelas->nama} sekarang memiliki {$totalSiswa} siswa dari kapasitas {$kelas->kapasitas}")
                ->send();
        }
    }
}
