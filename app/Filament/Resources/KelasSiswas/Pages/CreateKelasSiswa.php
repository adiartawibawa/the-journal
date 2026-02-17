<?php

namespace App\Filament\Resources\KelasSiswas\Pages;

use App\Filament\Resources\KelasSiswas\KelasSiswaResource;
use App\Models\Kelas;
use App\Models\KelasSiswa;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateKelasSiswa extends CreateRecord
{
    protected static string $resource = KelasSiswaResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Validate kapasitas before creating
        $kelas = Kelas::find($data['kelas_id']);
        $currentCount = $kelas->getJumlahSiswaAktifAttribute($data['tahun_ajaran_id']);

        if ($currentCount >= $kelas->kapasitas) {
            throw new \Exception("Kapasitas kelas {$kelas->nama} sudah penuh. Saat ini terisi {$currentCount} dari {$kelas->kapasitas} siswa.");
        }

        return parent::handleRecordCreation($data);
    }
}
