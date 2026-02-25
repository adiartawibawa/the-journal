<?php

namespace App\Filament\Widgets;

use App\Models\Kelas;
use App\Models\TahunAjaran;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class DaftarKelasTanpaWali extends TableWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Daftar Kelas Belum Berwali (Tahun Ajaran Aktif)';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 6;

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('kode')
                    ->label('Kode Kelas')
                    ->badge()
                    ->color('primary')
                    ->searchable(),

                TextColumn::make('nama')
                    ->label('Nama Kelas')
                    ->searchable(),

                TextColumn::make('tingkat')
                    ->label('Tingkat')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        '10' => 'success',
                        '11' => 'warning',
                        '12' => 'danger',
                        default => 'secondary',
                    }),

                TextColumn::make('jurusan')
                    ->label('Jurusan')
                    ->badge()
                    ->color('info')
                    ->placeholder('-'),

                TextColumn::make('kapasitas')
                    ->label('Kapasitas')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('jumlah_siswa_aktif')
                    ->label('Jml Siswa')
                    ->sortable(),

                TextColumn::make('sisa_kapasitas')
                    ->label('Sisa Kursi')
                    ->color(fn($state) => $state < 5 ? 'danger' : 'success')
                    ->sortable(),
            ])
            ->defaultSort('kode')
            ->emptyStateHeading('Semua kelas sudah memiliki Wali Kelas aktif.')
            ->emptyStateDescription('Kelas yang ditampilkan adalah kelas aktif tanpa wali kelas di tahun ajaran terpilih.')
            ->emptyStateIcon('heroicon-o-check-badge');
    }

    protected function getTableQuery(): Builder
    {
        $tahunAjaranId = $this->pageFilters['tahun_ajaran_id'] ?? TahunAjaran::getActive()?->id;

        if (!$tahunAjaranId) {
            return Kelas::query()->whereRaw('1 = 0'); // Return empty query
        }

        return Kelas::active() // Menggunakan scope active
            ->whereDoesntHave('waliKelas', function ($query) use ($tahunAjaranId) {
                $query->where('tahun_ajaran_id', $tahunAjaranId)
                    ->where('is_active', true);
            })
            ->withCount(['kelasSiswa as jumlah_siswa_aktif' => function ($query) use ($tahunAjaranId) {
                $query->where('status', 'aktif')
                    ->where('tahun_ajaran_id', $tahunAjaranId);
            }]);
    }
}
