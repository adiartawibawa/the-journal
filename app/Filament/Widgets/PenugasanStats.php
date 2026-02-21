<?php

namespace App\Filament\Widgets;

use App\Models\GuruMengajar;
use App\Models\TahunAjaran;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PenugasanStats extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $activeTa = TahunAjaran::findOrFail($this->pageFilters['tahun_ajaran_id'] ?? null);

        if (!$activeTa) {
            return [
                Stat::make('Status Akademik', 'Tahun Ajaran Inaktif')
                    ->description('Silahkan aktifkan tahun ajaran')
                    ->color('danger'),
            ];
        }

        $totalJam = GuruMengajar::where('tahun_ajaran_id', $activeTa->id)
            ->where('is_active', true)
            ->sum('jam_per_minggu');

        $totalPenugasan = GuruMengajar::where('tahun_ajaran_id', $activeTa->id)
            ->where('is_active', true)
            ->count();

        return [
            Stat::make('Total Beban Mengajar', $totalJam . ' Jam')
                ->description('Kumulatif jam per minggu')
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary'),
            Stat::make('Total Kelas Terisi', $totalPenugasan)
                ->description('Jumlah distribusi mapel ke kelas')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),
        ];
    }
}
