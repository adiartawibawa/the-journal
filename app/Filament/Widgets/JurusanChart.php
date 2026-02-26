<?php

namespace App\Filament\Widgets;

use App\Models\Kelas;
use App\Models\TahunAjaran;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class JurusanChart extends ChartWidget
{
    use HasWidgetShield;
    use InteractsWithPageFilters;

    protected ?string $heading = 'Distribusi Siswa per Jurusan';
    protected static ?int $sort = 3;
    protected ?array $options = [
        'plugins' => [
            'legend' => [
                'position' => 'bottom',
            ],
        ],
    ];

    protected function getData(): array
    {
        $tahunAjaranId = $this->pageFilters['tahun_ajaran_id'] ?? TahunAjaran::getActive()?->id;

        if (!$tahunAjaranId) {
            return $this->getEmptyData();
        }

        $activeTa = TahunAjaran::find($tahunAjaranId);

        if (!$activeTa) {
            return $this->getEmptyData();
        }

        $data = Kelas::active()
            ->withCount(['kelasSiswa as total_siswa' => function ($query) use ($activeTa) {
                $query->where('status', 'aktif')
                    ->where('tahun_ajaran_id', $activeTa->id);
            }])
            ->where('jurusan', '!=', '')
            ->whereNotNull('jurusan')
            ->get()
            ->groupBy('jurusan')
            ->map(fn($kelas) => $kelas->sum('total_siswa'))
            ->filter(fn($total) => $total > 0);

        if ($data->isEmpty()) {
            return $this->getEmptyData();
        }

        // GENERASI WARNA DINAMIS
        $backgroundColor = $data->keys()->map(function ($jurusan) {
            return $this->generateDynamicColor($jurusan);
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Siswa',
                    'data' => $data->values()->toArray(),
                    'backgroundColor' => $backgroundColor,
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $data->keys()->map(function ($jurusan) use ($data) {
                return $jurusan . ' (' . number_format($data[$jurusan]) . ' siswa)';
            })->toArray(),
        ];
    }

    /**
     * Menghasilkan warna HSL yang konsisten berdasarkan string input
     * @param string $string
     * @return string
     */
    private function generateDynamicColor(string $string): string
    {
        // Membuat hash dari string jurusan agar warna selalu sama untuk jurusan yang sama
        $hash = crc32($string);

        // Menggunakan HSL agar warna tetap terlihat cerah dan profesional
        // Hue (0-360), Saturation (60-70%), Lightness (50-60%)
        $hue = abs($hash % 360);

        return "hsl({$hue}, 70%, 55%)";
    }

    protected function getEmptyData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Siswa',
                    'data' => [1],
                    'backgroundColor' => ['#E5E7EB'],
                ],
            ],
            'labels' => ['Belum ada data'],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
