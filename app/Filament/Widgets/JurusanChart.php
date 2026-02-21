<?php

namespace App\Filament\Widgets;

use App\Models\KelasSiswa;
use App\Models\TahunAjaran;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class JurusanChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Distribusi Siswa per Jurusan';

    protected function getData(): array
    {
        $activeTa = TahunAjaran::findOrFail($this->pageFilters['tahun_ajaran_id'] ?? null);

        // Mengambil data jumlah siswa dikelompokkan berdasarkan jurusan di model Kelas
        $data = KelasSiswa::query()
            ->join('kelas', 'kelas_siswa.kelas_id', '=', 'kelas.id')
            ->where('kelas_siswa.tahun_ajaran_id', $activeTa->id)
            ->where('kelas_siswa.status', 'aktif')
            ->select('kelas.jurusan', DB::raw('count(*) as total'))
            ->groupBy('kelas.jurusan')
            ->pluck('total', 'jurusan');

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Siswa',
                    'data' => $data->values()->toArray(),
                    'backgroundColor' => ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                ],
            ],
            'labels' => $data->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
