<?php

namespace App\Filament\Widgets;

use App\Models\Siswa;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AbsensiSiswaOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;
    use HasWidgetShield;

    protected function getStats(): array
    {
        // Mengambil ID Tahun Ajaran dari filter halaman (header)
        $tahunAjaranId = $this->filters['tahun_ajaran_id'] ?? null;

        // Memanggil data dinamis yang sudah ter-scope
        $data = Siswa::getAbsensiStats($tahunAjaranId);

        return [
            // Stat::make('Hadir', $data['Hadir'])
            //     ->description('Total kehadiran siswa')
            //     ->descriptionIcon('heroicon-m-check-badge')
            //     ->color('success'),

            Stat::make('Sakit', $data['Sakit'])
                ->description('Siswa sakit')
                ->descriptionIcon('heroicon-m-heart')
                ->color('warning'),

            Stat::make('Izin', $data['Izin'])
                ->description('Siswa izin')
                ->descriptionIcon('heroicon-m-envelope')
                ->color('info'),

            Stat::make('Alpha', $data['Alpha'])
                ->description('Tanpa keterangan')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
