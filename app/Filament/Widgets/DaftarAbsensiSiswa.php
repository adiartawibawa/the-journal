<?php

namespace App\Filament\Widgets;

use App\Models\Siswa;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class DaftarAbsensiSiswa extends TableWidget
{
    use InteractsWithPageFilters;
    use HasWidgetShield;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Daftar Kehadiran Siswa';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Mengambil siswa yang setidaknya memiliki satu record di AbsensiSiswa
                Siswa::query()
                    ->whereHas('riwayatAbsensi', function (Builder $query) {
                        $query->when(
                            $this->filters['tahun_ajaran_id'] ?? null,
                            fn($q, $ta) => $q->whereHas('jurnal', fn($j) => $j->where('tahun_ajaran_id', $ta))
                        );
                    })
                    ->with(['user', 'riwayatAbsensi'])
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama Siswa')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nisn')
                    ->label('NISN')
                    ->toggleable(),

                // Menampilkan jumlah akumulasi status absensi
                TextColumn::make('sakit_count')
                    ->label('Sakti')
                    ->badge()
                    ->color('warning')
                    ->getStateUsing(fn($record) => $this->countStatus($record, 'Sakit')),

                TextColumn::make('izin_count')
                    ->label('Izin')
                    ->badge()
                    ->color('info')
                    ->getStateUsing(fn($record) => $this->countStatus($record, 'Izin')),

                TextColumn::make('alpha_count')
                    ->label('Alpha')
                    ->badge()
                    ->color('danger')
                    ->getStateUsing(fn($record) => $this->countStatus($record, 'Alpha')),

                TextColumn::make('terakhir_absen')
                    ->label('Absensi Terakhir')
                    ->description(fn($record) => $record->riwayatAbsensi->last()?->status ?? '-')
                    ->getStateUsing(fn($record) => $record->riwayatAbsensi->last()?->created_at?->diffForHumans() ?? '-'),
            ])
            ->actions([
                Action::make('view_profile')
                    ->label('Lihat Profil')
                    ->icon('heroicon-m-user')
                    ->url(fn($record) => "/admin/siswas/{$record->id}"),
            ]);
    }

    /**
     * Helper untuk menghitung status berdasarkan filter tahun ajaran
     */
    protected function countStatus($record, $status): int
    {
        $taId = $this->filters['tahun_ajaran_id'] ?? null;

        return $record->riwayatAbsensi
            ->when($taId, function ($collection) use ($taId) {
                return $collection->filter(fn($absensi) => $absensi->jurnal?->tahun_ajaran_id === $taId);
            })
            ->where('status', $status)
            ->count();
    }
}
