<?php

namespace App\Filament\Widgets;

use App\Models\Kelas;
use App\Models\TahunAjaran;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KelasTanpaWaliStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        // Mendapatkan Tahun Ajaran yang sedang aktif
        $tahunAktif = TahunAjaran::where('is_active', true)->first();

        if (!$tahunAktif) {
            return [
                Stat::make('Peringatan', 'Tahun Ajaran Aktif Tidak Ditemukan')
                    ->description('Aktifkan satu tahun ajaran terlebih dahulu')
                    ->color('danger'),
            ];
        }

        // Menghitung kelas yang tidak memiliki wali kelas aktif di tahun ajaran tersebut
        $count = Kelas::where('is_active', true)
            ->whereDoesntHave('waliKelas', function ($query) use ($tahunAktif) {
                $query->where('tahun_ajaran_id', $tahunAktif->id)
                    ->where('is_active', true);
            })->count();

        return [
            Stat::make('Kelas Tanpa Wali', $count)
                ->description('Total kelas aktif yang belum memiliki penanggung jawab')
                ->descriptionIcon($count > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-badge')
                ->color($count > 0 ? 'danger' : 'success'),
        ];
    }
}
