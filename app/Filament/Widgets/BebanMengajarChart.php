<?php

namespace App\Filament\Widgets;

use App\Models\GuruMengajar;
use App\Models\TahunAjaran;
use Filament\Widgets\ChartWidget;

class BebanMengajarChart extends ChartWidget
{
    protected ?string $heading = 'Distribusi Beban Mengajar (Jam/Minggu)';

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $tahunAktif = TahunAjaran::getActive();

        // Mengambil data beban jam per guru
        $data = GuruMengajar::with('guru.user')
            ->where('tahun_ajaran_id', $tahunAktif?->id)
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
