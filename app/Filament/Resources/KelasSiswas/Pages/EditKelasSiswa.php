<?php

namespace App\Filament\Resources\KelasSiswas\Pages;

use App\Filament\Resources\KelasSiswas\KelasSiswaResource;
use App\Models\Kelas;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditKelasSiswa extends EditRecord
{
    protected static string $resource = KelasSiswaResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Validate kapasitas if kelas or tahun ajaran changed
        if ($record->kelas_id !== $data['kelas_id'] || $record->tahun_ajaran_id !== $data['tahun_ajaran_id']) {
            $kelas = Kelas::find($data['kelas_id']);

            // Get current count for the new class and tahun ajaran
            $currentCount = $kelas->getJumlahSiswaAktifAttribute($data['tahun_ajaran_id']);

            // If we're moving the student to a different class within the same tahun ajaran,
            // we need to account for the current record not being counted yet
            if ($record->tahun_ajaran_id === $data['tahun_ajaran_id']) {
                // If the student is currently active in this tahun ajaran, they're already counted
                if ($record->status === 'aktif') {
                    // If moving to different class in same tahun ajaran, count remains the same
                    // because we're removing from old class and adding to new class
                    $currentCount = $currentCount; // No adjustment needed
                }
            }

            if ($currentCount >= $kelas->kapasitas) {
                throw new \Exception("Kapasitas kelas {$kelas->nama} sudah penuh. Saat ini terisi {$currentCount} dari {$kelas->kapasitas} siswa.");
            }
        }

        return parent::handleRecordUpdate($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
