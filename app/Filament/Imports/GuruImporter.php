<?php

namespace App\Filament\Imports;

use App\Models\Guru;
use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Number;

class GuruImporter extends Importer
{
    protected static ?string $model = Guru::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('user')
                ->relationship(
                    resolveUsing: function (string $state): ?User {
                        return User::query()
                            ->where('email', $state)
                            ->first();
                    }
                )->label('Nama Guru')->requiredMapping(),
            // ImportColumn::make('user')->relationship(resolveUsing: ['name'])->label('Nama Guru')->requiredMapping(),
            // ImportColumn::make('user')->relationship(resolveUsing: ['email'])->label('Email Aktif')->requiredMapping(),
            // ImportColumn::make('user')->relationship(resolveUsing: ['password'])->label('Password')->requiredMapping(),
            ImportColumn::make('nuptk')->label('NUPTK')->requiredMapping(),
            ImportColumn::make('status_kepegawaian')->label('Status Kepegawaian (PNS/PPPK/Guru Honor/Staff Honor/Kontrak)'),
            ImportColumn::make('bidang_studi')->label('Bidang Studi'),
            ImportColumn::make('golongan')->label('Golongan (I/II/III/IV)'),
            ImportColumn::make('tanggal_masuk')->label('Tanggal Masuk (MM/DD/YYYY)'),
            ImportColumn::make('pendidikan_terakhir')->label('Pendidikan Terakhir (SMA/SMK/D3/S1/S2/S3)'),
        ];
    }

    public function resolveRecord(): ?Guru
    {
        return Guru::firstOrNew([
            'nuptk' => $this->data['nuptk'],
        ]);
    }

    // TODO : Assign Role (super_admin, admin, teacher, student)

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your guru import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
