<?php

namespace App\Filament\Widgets;

use App\Models\KelasSiswa;
use App\Models\TahunAjaran;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class JurusanChart extends ChartWidget
{
    protected ?string $heading = 'Distribusi Siswa per Jurusan';

    protected function getData(): array
    {
        $activeTA = TahunAjaran::where('is_active', true)->first();

        // Mengambil data jumlah siswa dikelompokkan berdasarkan jurusan di model Kelas
        $data = KelasSiswa::query()
            ->join('kelas', 'kelas_siswa.kelas_id', '=', 'kelas.id')
            ->where('kelas_siswa.tahun_ajaran_id', $activeTA?->id)
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
