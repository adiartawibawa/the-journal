<?php

namespace App\Filament\Widgets;

use App\Models\Jurnal;
use App\Models\TahunAjaran;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Log;

class JurnalPeriodicStats extends StatsOverviewWidget
{
    use HasWidgetShield;
    use InteractsWithPageFilters;

    protected ?string $heading = 'Statistik Jurnal Mengajar';
    protected static ?int $sort = 4;

    // Polling interval untuk update real-time (opsional)
    protected  ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        // Mendapatkan tahun ajaran dari filter atau default ke aktif
        $tahunAjaranId = $this->pageFilters['tahun_ajaran_id'] ?? TahunAjaran::getActive()?->id;

        if (!$tahunAjaranId) {
            return $this->getEmptyStats('Tahun ajaran tidak ditemukan');
        }

        $tahunAjaran = TahunAjaran::find($tahunAjaranId);

        if (!$tahunAjaran) {
            return $this->getEmptyStats('Tahun ajaran tidak valid');
        }

        try {
            // Base query dengan filter tahun ajaran
            $baseQuery = Jurnal::query()
                ->where('tahun_ajaran_id', $tahunAjaran->id);

            // Statistik Hari Ini
            $hariIni = (clone $baseQuery)
                ->whereDate('tanggal', Carbon::today())
                ->count();

            // Statistik Minggu Ini
            $mingguIni = (clone $baseQuery)
                ->whereBetween('tanggal', [
                    Carbon::now()->startOfWeek(Carbon::MONDAY),
                    Carbon::now()->endOfWeek(Carbon::SUNDAY)
                ])
                ->count();

            // Statistik Bulan Ini
            $bulanIni = (clone $baseQuery)
                ->whereMonth('tanggal', Carbon::now()->month)
                ->whereYear('tanggal', Carbon::now()->year)
                ->count();

            // Statistik Tahun Ini (dalam tahun ajaran)
            $tahunIni = (clone $baseQuery)
                ->whereBetween('tanggal', [
                    $tahunAjaran->tanggal_awal,
                    $tahunAjaran->tanggal_akhir
                ])
                ->count();

            // Data chart untuk tren mingguan
            $weeklyChartData = $this->getWeeklyChartData($tahunAjaran->id);

            // Data chart untuk tren bulanan
            $monthlyChartData = $this->getMonthlyChartData($tahunAjaran->id);

            // Data chart untuk perbandingan
            $comparisonChartData = [
                $hariIni,
                $mingguIni > 0 ? round($mingguIni / 7, 1) : 0, // Rata-rata per hari dalam minggu
                $bulanIni > 0 ? round($bulanIni / 30, 1) : 0, // Rata-rata per hari dalam bulan
            ];

            return [
                Stat::make('Jurnal Hari Ini', number_format($hariIni))
                    ->description($this->getHariIniDescription($hariIni, $mingguIni))
                    ->descriptionIcon($this->getHariIniIcon($hariIni))
                    ->color($this->getHariIniColor($hariIni))
                    ->chart($comparisonChartData)
                    ->extraAttributes([
                        'class' => 'cursor-pointer',
                        'title' => 'Klik untuk melihat detail jurnal hari ini',
                    ]),

                Stat::make('Jurnal Minggu Ini', number_format($mingguIni))
                    ->description($this->getMingguIniDescription($mingguIni, $tahunIni))
                    ->descriptionIcon('heroicon-m-calendar-days')
                    ->color($this->getMingguIniColor($mingguIni))
                    ->chart($weeklyChartData)
                    ->extraAttributes([
                        'class' => 'cursor-pointer',
                        'title' => 'Tren jurnal 7 hari terakhir',
                    ]),

                Stat::make('Jurnal Bulan Ini', number_format($bulanIni))
                    ->description($this->getBulanIniDescription($bulanIni, $tahunIni))
                    ->descriptionIcon('heroicon-m-chart-bar')
                    ->color($this->getBulanIniColor($bulanIni))
                    ->chart($monthlyChartData)
                    ->extraAttributes([
                        'class' => 'cursor-pointer',
                        'title' => 'Tren jurnal 30 hari terakhir',
                    ]),
            ];
        } catch (\Exception $e) {
            Log::error('Error in JurnalPeriodicStats: ' . $e->getMessage());

            return [
                Stat::make('Error', 'Gagal memuat data')
                    ->description('Silahkan coba lagi')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger'),
            ];
        }
    }

    /**
     * Mendapatkan data chart untuk tren mingguan
     */
    protected function getWeeklyChartData(string $tahunAjaranId): array
    {
        $data = [];
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);

        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);

            $count = Jurnal::query()
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->whereDate('tanggal', $date)
                ->count();

            $data[] = $count;
        }

        return $data;
    }

    /**
     * Mendapatkan data chart untuk tren bulanan (per minggu)
     */
    protected function getMonthlyChartData(string $tahunAjaranId): array
    {
        $data = [];
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Bagi bulan menjadi 4 minggu
        $weekRanges = [
            [$startOfMonth->copy(), $startOfMonth->copy()->addDays(6)],
            [$startOfMonth->copy()->addDays(7), $startOfMonth->copy()->addDays(13)],
            [$startOfMonth->copy()->addDays(14), $startOfMonth->copy()->addDays(20)],
            [$startOfMonth->copy()->addDays(21), $endOfMonth],
        ];

        foreach ($weekRanges as $range) {
            $count = Jurnal::query()
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->whereBetween('tanggal', [$range[0], $range[1]])
                ->count();

            $data[] = $count;
        }

        return $data;
    }

    /**
     * Mendapatkan deskripsi untuk statistik hari ini
     */
    protected function getHariIniDescription(int $hariIni, int $mingguIni): string
    {
        if ($hariIni === 0) {
            return 'Belum ada jurnal hari ini';
        }

        $rataRataHarian = $mingguIni > 0 ? round($mingguIni / 7, 1) : 0;

        if ($rataRataHarian > 0) {
            if ($hariIni > $rataRataHarian) {
                $persen = round(($hariIni - $rataRataHarian) / $rataRataHarian * 100);
                return "↑ {$persen}% dari rata-rata mingguan";
            } elseif ($hariIni < $rataRataHarian) {
                $persen = round(($rataRataHarian - $hariIni) / $rataRataHarian * 100);
                return "↓ {$persen}% dari rata-rata mingguan";
            }
        }

        return 'Total jurnal hari ini';
    }

    /**
     * Mendapatkan deskripsi untuk statistik minggu ini
     */
    protected function getMingguIniDescription(int $mingguIni, int $tahunIni): string
    {
        if ($mingguIni === 0) {
            return 'Belum ada jurnal minggu ini';
        }

        $rataRataMingguan = $tahunIni > 0 ? round($tahunIni / 52, 1) : 0;

        if ($rataRataMingguan > 0) {
            $persenDariTarget = round(($mingguIni / $rataRataMingguan) * 100);
            return "{$persenDariTarget}% dari rata-rata tahunan";
        }

        return 'Akumulasi jurnal minggu ini';
    }

    /**
     * Mendapatkan deskripsi untuk statistik bulan ini
     */
    protected function getBulanIniDescription(int $bulanIni, int $tahunIni): string
    {
        if ($bulanIni === 0) {
            return 'Belum ada jurnal bulan ini';
        }

        $rataRataBulanan = $tahunIni > 0 ? round($tahunIni / 12, 1) : 0;

        if ($rataRataBulanan > 0) {
            if ($bulanIni > $rataRataBulanan) {
                return "Di atas rata-rata bulanan";
            } elseif ($bulanIni < $rataRataBulanan) {
                return "Di bawah rata-rata bulanan";
            }
        }

        return 'Total performa bulanan';
    }

    /**
     * Mendapatkan icon untuk statistik hari ini
     */
    protected function getHariIniIcon(int $hariIni): string
    {
        if ($hariIni === 0) {
            return 'heroicon-m-face-frown';
        }
        return 'heroicon-m-calendar';
    }

    /**
     * Mendapatkan warna untuk statistik hari ini
     */
    protected function getHariIniColor(int $hariIni): string
    {
        return match (true) {
            $hariIni >= 10 => 'success',
            $hariIni >= 5 => 'warning',
            $hariIni > 0 => 'info',
            default => 'danger',
        };
    }

    /**
     * Mendapatkan warna untuk statistik minggu ini
     */
    protected function getMingguIniColor(int $mingguIni): string
    {
        return match (true) {
            $mingguIni >= 50 => 'success',
            $mingguIni >= 25 => 'warning',
            $mingguIni > 0 => 'info',
            default => 'danger',
        };
    }

    /**
     * Mendapatkan warna untuk statistik bulan ini
     */
    protected function getBulanIniColor(int $bulanIni): string
    {
        return match (true) {
            $bulanIni >= 200 => 'success',
            $bulanIni >= 100 => 'warning',
            $bulanIni > 0 => 'info',
            default => 'danger',
        };
    }

    /**
     * Mendapatkan statistik kosong
     */
    protected function getEmptyStats(string $message): array
    {
        return [
            Stat::make('Jurnal Hari Ini', '0')
                ->description($message)
                ->descriptionIcon('heroicon-m-information-circle')
                ->color('gray'),

            Stat::make('Jurnal Minggu Ini', '0')
                ->description($message)
                ->color('gray'),

            Stat::make('Jurnal Bulan Ini', '0')
                ->description($message)
                ->color('gray'),
        ];
    }
}
