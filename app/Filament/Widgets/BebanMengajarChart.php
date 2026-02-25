<?php

namespace App\Filament\Widgets;

use App\Models\GuruMengajar;
use App\Models\TahunAjaran;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\Log;

class BebanMengajarChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Distribusi Beban Mengajar (Jam/Minggu)';

    protected ?string $pollingInterval = null;

    protected ?array $options = [
        'plugins' => [
            'legend' => [
                'display' => false,
            ],
        ],
        'scales' => [
            'y' => [
                'beginAtZero' => true,
                'title' => [
                    'display' => true,
                    'text' => 'Jam per Minggu',
                ],
            ],
        ],
    ];

    protected function getData(): array
    {
        try {
            $tahunAjaranId = $this->pageFilters['tahun_ajaran_id'] ?? TahunAjaran::getActive()?->id;

            if (!$tahunAjaranId) {
                return $this->getEmptyData();
            }

            $activeTa = TahunAjaran::find($tahunAjaranId);

            if (!$activeTa) {
                return $this->getEmptyData();
            }

            // Mengambil data beban jam per guru
            $data = GuruMengajar::with('guru.user')
                ->where('tahun_ajaran_id', $activeTa->id)
                ->where('is_active', true)
                ->get()
                ->groupBy(function ($item) {
                    return $item->guru->user->name ?? 'Guru Tanpa Nama';
                })
                ->map(fn($group) => $group->sum('jam_per_minggu'))
                ->sortDesc()
                ->take(10); // Batasi 10 guru teratas

            if ($data->isEmpty()) {
                return $this->getEmptyData();
            }

            return [
                'datasets' => [
                    [
                        'label' => 'Jam Mengajar',
                        'data' => $data->values()->toArray(),
                        'backgroundColor' => '#36A2EB',
                        'borderColor' => '#1E4B8F',
                        'borderWidth' => 1,
                        'borderRadius' => 5,
                    ],
                ],
                'labels' => $data->keys()->toArray(),
            ];
        } catch (\Exception $e) {
            Log::error('Error loading BebanMengajarChart: ' . $e->getMessage());
            return $this->getEmptyData();
        }
    }

    protected function getEmptyData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Jam Mengajar',
                    'data' => [],
                    'backgroundColor' => '#E5E7EB',
                ],
            ],
            'labels' => ['Tidak ada data'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
