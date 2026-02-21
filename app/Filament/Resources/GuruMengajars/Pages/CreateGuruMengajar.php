<?php

namespace App\Filament\Resources\GuruMengajars\Pages;

use App\Filament\Resources\GuruMengajars\GuruMengajarResource;
use App\Models\GuruMengajar;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateGuruMengajar extends CreateRecord
{
    protected static string $resource = GuruMengajarResource::class;

    protected function beforeCreate(): void
    {
        $exists = GuruMengajar::where([
            'tahun_ajaran_id' => $this->data['tahun_ajaran_id'],
            'kelas_id' => $this->data['kelas_id'],
            'mapel_id' => $this->data['mapel_id'],
            'is_active' => true,
        ])->exists();

        if ($exists) {
            Notification::make()
                ->title('Gagal Menyimpan')
                ->body('Mata pelajaran ini sudah memiliki pengajar aktif di kelas tersebut.')
                ->danger()
                ->send();

            $this->halt();
        }
    }
}
