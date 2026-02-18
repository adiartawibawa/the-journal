<?php

namespace App\Filament\Resources\KelasSiswas\Tables;

use App\Models\TahunAjaran;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KelasSiswasTable
{
    public static function configure(Table $table): Table
    {
        // Mendapatkan ID Tahun Ajaran yang sedang aktif
        $activeTahunAjaran = TahunAjaran::where('is_active', true)->first();

        return $table
            ->modifyQueryUsing(function (Builder $query) use ($activeTahunAjaran) {
                /** * Kita memodifikasi query Kelas agar hanya menampilkan kelas
                 * yang memiliki record di kelas_siswa pada tahun ajaran aktif
                 */
                return $query->whereHas('kelasSiswa', function ($q) use ($activeTahunAjaran) {
                    $q->where('tahun_ajaran_id', $activeTahunAjaran?->id);
                })
                    ->with(['kelasSiswa.siswa.user'])
                    // Eager load count untuk efisiensi performa (menghindari N+1 query)
                    ->withCount(['kelasSiswa as jumlah_siswa' => function ($q) use ($activeTahunAjaran) {
                        $q->where('tahun_ajaran_id', $activeTahunAjaran?->id)
                            ->where('status', 'aktif');
                    }]);
            })
            ->columns([
                TextColumn::make('kode')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('nama')
                    ->label('Nama Kelas')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tingkat')
                    ->sortable(),

                TextColumn::make('jurusan')
                    ->badge()
                    ->color('info'),

                ImageColumn::make('siswa.user.avatar_url')
                    ->label('Siswa')
                    ->imageHeight(40)
                    ->circular()
                    ->stacked()
                    ->limit(5)
                    ->getStateUsing(function ($record) {
                        // Ambil URL avatar dari semua siswa yang tergabung di kelas tersebut
                        return $record->kelasSiswa->map(fn($ks) => $ks->siswa->user->avatar_url)->toArray();
                    }),

                TextColumn::make('jumlah_siswa')
                    ->label('Jumlah Siswa Aktif')
                    ->suffix(' Siswa')
                    ->alignCenter()
                    ->color(fn($state, $record) => $state >= $record->kapasitas ? 'danger' : 'success')
                    ->description(fn($record): string => "Kapasitas: {$record->kapasitas}"),

                IconColumn::make('is_active')
                    ->label('Status Kelas')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('tahun_ajaran')
                    ->relationship('kelasSiswa.tahunAjaran', 'nama')
                    ->default($activeTahunAjaran?->id)
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Kelola Siswa')
                    ->icon('heroicon-m-user-group')
                    ->color('success'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('kelas.nama', 'asc');
    }
}
