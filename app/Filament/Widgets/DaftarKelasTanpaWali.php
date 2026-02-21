<?php

namespace App\Filament\Widgets;

use App\Models\Kelas;
use App\Models\TahunAjaran;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class DaftarKelasTanpaWali extends TableWidget
{
    protected static ?string $heading = 'Daftar Kelas Belum Berwali (Tahun Ajaran Aktif)';

    public function table(Table $table): Table
    {
        $tahunAktif = TahunAjaran::where('is_active', true)->first();

        return $table
            ->query(
                // Query kelas aktif yang tidak memiliki wali kelas aktif
                Kelas::where('is_active', true)
                    ->whereDoesntHave('waliKelas', function ($query) use ($tahunAktif) {
                        if ($tahunAktif) {
                            $query->where('tahun_ajaran_id', $tahunAktif->id)
                                ->where('is_active', true);
                        }
                    })
            )
            ->columns([
                TextColumn::make('kode')
                    ->label('Kode Kelas')
                    ->badge(),
                TextColumn::make('nama')
                    ->label('Nama Kelas'),
                TextColumn::make('tingkat')
                    ->label('Tingkat'),
                TextColumn::make('jurusan')
                    ->label('Jurusan')
                    ->placeholder('-'),
            ])
            ->emptyStateHeading('Semua kelas sudah memiliki Wali Kelas aktif.');
    }
}
