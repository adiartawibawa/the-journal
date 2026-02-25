<?php

namespace App\Filament\Widgets;

use App\Models\Kelas;
use App\Models\TahunAjaran;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KelasTanpaWaliStats extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $tahunAjaranId = $this->pageFilters['tahun_ajaran_id'] ?? TahunAjaran::getActive()?->id;

        if (!$tahunAjaranId) {
            return [
                Stat::make('Kelas Tanpa Wali', 'N/A')
                    ->description('Tahun ajaran tidak ditemukan')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger'),
            ];
        }

        $tahunAktif = TahunAjaran::find($tahunAjaranId);

        if (!$tahunAktif) {
            return [
                Stat::make('Kelas Tanpa Wali', 'N/A')
                    ->description('Tahun ajaran tidak valid')
                    ->color('danger'),
            ];
        }

        // Menggunakan scope active dan menghitung kelas tanpa wali
        $count = Kelas::active()
            ->whereDoesntHave('waliKelas', function ($query) use ($tahunAktif) {
                $query->where('tahun_ajaran_id', $tahunAktif->id)
                    ->where('is_active', true);
            })->count();

        // Mendapatkan total kelas aktif
        $totalKelas = Kelas::active()->count();

        return [
            Stat::make('Kelas Tanpa Wali', $count)
                ->description($count > 0
                    ? "{$count} dari {$totalKelas} kelas perlu wali kelas"
                    : "Semua {$totalKelas} kelas memiliki wali")
                ->descriptionIcon($count > 0
                    ? 'heroicon-m-exclamation-triangle'
                    : 'heroicon-m-check-badge')
                ->color($count > 0 ? 'danger' : 'success')
                ->chart([$count, $totalKelas - $count]),
        ];
    }
}
