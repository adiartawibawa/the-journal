<?php

namespace App\Filament\Widgets;

use App\Models\GuruMengajar;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PenugasanStats extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $tahunAjaranId = $this->pageFilters['tahun_ajaran_id'] ?? TahunAjaran::getActive()?->id;

        if (!$tahunAjaranId) {
            return [
                Stat::make('Status Akademik', 'Pilih Tahun Ajaran')
                    ->description('Gunakan filter untuk memilih tahun ajaran')
                    ->descriptionIcon('heroicon-m-funnel')
                    ->color('warning'),
            ];
        }

        $activeTa = TahunAjaran::find($tahunAjaranId);

        if (!$activeTa) {
            return [
                Stat::make('Status Akademik', 'Tahun Ajaran Tidak Valid')
                    ->description('Silahkan pilih tahun ajaran yang valid')
                    ->color('danger'),
            ];
        }

        // Statistik penugasan
        $totalJam = GuruMengajar::where('tahun_ajaran_id', $activeTa->id)
            ->where('is_active', true)
            ->sum('jam_per_minggu');

        $totalPenugasan = GuruMengajar::where('tahun_ajaran_id', $activeTa->id)
            ->where('is_active', true)
            ->count();

        $totalGuru = GuruMengajar::where('tahun_ajaran_id', $activeTa->id)
            ->where('is_active', true)
            ->distinct('guru_id')
            ->count('guru_id');

        $rataJam = $totalGuru > 0 ? round($totalJam / $totalGuru, 1) : 0;

        // Cakupan kelas
        $kelasDenganPenugasan = GuruMengajar::where('tahun_ajaran_id', $activeTa->id)
            ->where('is_active', true)
            ->distinct('kelas_id')
            ->count('kelas_id');

        $totalKelas = Kelas::active()->count();
        $persentaseCakupan = $totalKelas > 0
            ? round(($kelasDenganPenugasan / $totalKelas) * 100, 1)
            : 0;

        return [
            Stat::make('Total Beban Mengajar', number_format($totalJam) . ' Jam')
                ->description("Rata-rata {$rataJam} jam/guru")
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary')
                ->chart([$totalJam, $totalJam * 0.8, $totalJam * 1.2]),

            Stat::make('Total Penugasan', number_format($totalPenugasan))
                ->description("{$totalGuru} guru mengajar")
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),

            Stat::make('Cakupan Kelas', $persentaseCakupan . '%')
                ->description("{$kelasDenganPenugasan} dari {$totalKelas} kelas terisi")
                ->descriptionIcon('heroicon-m-building-library')
                ->color($this->getCakupanColor($persentaseCakupan)),
        ];
    }

    protected function getCakupanColor(float $percentage): string
    {
        return match (true) {
            $percentage >= 90 => 'success',
            $percentage >= 70 => 'warning',
            default => 'danger',
        };
    }
}
