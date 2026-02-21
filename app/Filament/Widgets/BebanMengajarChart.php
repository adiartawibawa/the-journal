<?php

namespace App\Filament\Widgets;

use App\Models\GuruMengajar;
use App\Models\TahunAjaran;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class BebanMengajarChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Distribusi Beban Mengajar (Jam/Minggu)';

    protected function getData(): array
    {
        $activeTa = TahunAjaran::findOrFail($this->pageFilters['tahun_ajaran_id'] ?? null);

        // Mengambil data beban jam per guru
        $data = GuruMengajar::with('guru.user')
            ->where('tahun_ajaran_id', $activeTa->id)
            ->where('is_active', true)
            ->get()
            ->groupBy('guru.user.name')
            ->map(fn($group) => $group->sum('jam_per_minggu'));

        return [
            'datasets' => [
                [
                    'label' => 'Jam Mengajar',
                    'data' => $data->values()->toArray(),
                    'backgroundColor' => '#36A2EB',
                ],
            ],
            'labels' => $data->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
