<?php

namespace App\Filament\Widgets;

use App\Models\Kelas;
use App\Models\KelasSiswa;
use App\Models\TahunAjaran;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SiswaStatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        $activeTa = TahunAjaran::findOrFail($this->pageFilters['tahun_ajaran_id'] ?? null);

        $totalSiswaAktif = KelasSiswa::where('tahun_ajaran_id', $activeTa?->id)
            ->where('status', 'aktif')
            ->count();

        $totalKapasitas = Kelas::where('is_active', true)->sum('kapasitas');

        $persentaseKetersediaan = $totalKapasitas > 0
            ? round(($totalSiswaAktif / $totalKapasitas) * 100, 1)
            : 0;

        return [
            Stat::make('Siswa Aktif', $totalSiswaAktif)
                ->description("Tahun Ajaran: {$activeTa?->nama_semester}")
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Okupansi Kelas', "{$persentaseKetersediaan}%")
                ->description("Terpakai {$totalSiswaAktif} dari {$totalKapasitas} kursi")
                ->chart([7, 3, 4, 5, 6, 3, $persentaseKetersediaan])
                ->color($persentaseKetersediaan > 90 ? 'danger' : 'info'),

            Stat::make('Total Kelas Aktif', Kelas::where('is_active', true)->count())
                ->description('Kelas terdaftar di sistem')
                ->descriptionIcon('heroicon-m-academic-cap'),
        ];
    }
}
