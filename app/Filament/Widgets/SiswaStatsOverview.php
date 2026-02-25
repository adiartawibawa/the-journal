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

    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $tahunAjaranId = $this->pageFilters['tahun_ajaran_id'] ?? TahunAjaran::getActive()?->id;

        if (!$tahunAjaranId) {
            return $this->getEmptyStats();
        }

        $activeTa = TahunAjaran::find($tahunAjaranId);

        if (!$activeTa) {
            return $this->getEmptyStats();
        }

        // Menggunakan model Kelas untuk menghitung statistik
        $kelasAktif = Kelas::active()->get();

        $totalKelas = $kelasAktif->count();
        $totalKapasitas = $kelasAktif->sum('kapasitas');

        // Menghitung total siswa aktif menggunakan method dari model Kelas
        $totalSiswaAktif = collect($kelasAktif)->sum(function ($kelas) use ($activeTa) {
            return $kelas->kelasSiswa()
                ->where('status', 'aktif')
                ->where('tahun_ajaran_id', $activeTa->id)
                ->count();
        });

        $persentaseKetersediaan = $totalKapasitas > 0
            ? round(($totalSiswaAktif / $totalKapasitas) * 100, 1)
            : 0;

        // Menghitung kelas dengan kapasitas kritis (< 10% sisa)
        $kelasKritis = $kelasAktif->filter(function ($kelas) use ($activeTa) {
            $sisa = $kelas->kapasitas - $kelas->kelasSiswa()
                ->where('status', 'aktif')
                ->where('tahun_ajaran_id', $activeTa->id)
                ->count();
            return $sisa < ceil($kelas->kapasitas * 0.1); // Sisa < 10% kapasitas
        })->count();

        return [
            Stat::make('Total Siswa Aktif', number_format($totalSiswaAktif))
                ->description("T.A. {$activeTa->nama_semester}")
                ->descriptionIcon('heroicon-m-users')
                ->color('success')
                ->chart([$totalSiswaAktif, $totalKapasitas]),

            Stat::make('Okupansi Kelas', $persentaseKetersediaan . '%')
                ->description("Terpakai {$totalSiswaAktif} dari {$totalKapasitas} kursi")
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color($this->getOccupancyColor($persentaseKetersediaan))
                ->chart([$persentaseKetersediaan, 100 - $persentaseKetersediaan]),

            Stat::make('Kondisi Kelas', "{$kelasKritis} Kelas Kritis")
                ->description("{$totalKelas} total kelas aktif")
                ->descriptionIcon($kelasKritis > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($kelasKritis > 0 ? 'warning' : 'success'),
        ];
    }

    protected function getEmptyStats(): array
    {
        return [
            Stat::make('Total Siswa Aktif', 'N/A')
                ->description('Pilih tahun ajaran terlebih dahulu')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),

            Stat::make('Okupansi Kelas', '0%')
                ->description('Belum ada data')
                ->color('gray'),

            Stat::make('Kondisi Kelas', '0 Kelas Kritis')
                ->description('0 total kelas aktif')
                ->color('gray'),
        ];
    }

    protected function getOccupancyColor(float $percentage): string
    {
        return match (true) {
            $percentage >= 95 => 'danger',   // Penuh sesak
            $percentage >= 80 => 'warning',  // Hampir penuh
            $percentage >= 50 => 'success',  // Optimal
            $percentage >= 25 => 'info',     // Cukup
            default => 'gray',                // Sepi
        };
    }
}
