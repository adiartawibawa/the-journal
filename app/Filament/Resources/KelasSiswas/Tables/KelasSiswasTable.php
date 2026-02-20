<?php

namespace App\Filament\Resources\KelasSiswas\Tables;

use App\Models\Guru;
use App\Models\TahunAjaran;
use App\Models\WaliKelas;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KelasSiswasTable
{
    public static function configure(Table $table): Table
    {
        $activeTaId = \App\Models\TahunAjaran::where('is_active', true)->first()?->id;

        return $table
            ->columns([
                TextColumn::make('kode')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('nama')
                    ->label('Nama Kelas')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => "Tingkat: {$record->tingkat} | Jurusan: {$record->jurusan}"),

                ImageColumn::make('wali_kelas_avatar')
                    ->label('Wali Kelas')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-avatar.png'))
                    ->getStateUsing(function ($record) {
                        // Ambil wali kelas pertama dari koleksi yang sudah di-filter via eager load
                        return $record->waliKelas->first()?->guru?->user?->avatar_url;
                    }),

                TextColumn::make('wali_kelas_nama')
                    ->label('Nama Wali')
                    ->getStateUsing(function ($record) {
                        $guru = $record->waliKelas->first()?->guru;
                        return $guru ? $guru->user->name : '- Belum Ada -';
                    })
                    ->description(fn($record) => $record->waliKelas->first()?->guru?->nuptk ?? 'NUPTK: -')
                    ->wrap(),

                ImageColumn::make('siswa_avatar')
                    ->label('Siswa')
                    ->circular()
                    ->stacked()
                    ->limit(5)
                    ->getStateUsing(function ($record) {
                        return $record->kelasSiswa
                            ->map(fn($ks) => $ks->siswa->user->avatar_url)
                            ->toArray();
                    }),

                TextColumn::make('jumlah_siswa_aktif')
                    ->label('Total Siswa')
                    ->badge()
                    ->alignCenter()
                    ->color(fn($state, $record) => $state >= $record->kapasitas ? 'danger' : 'success'),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('tahun_ajaran')
                    ->relationship('kelasSiswa.tahunAjaran', 'nama')
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->nama_semester)
                    ->default($activeTaId)
                    ->query(function (Builder $query, array $data) use ($activeTaId) {
                        // Gunakan ID dari filter, jika kosong gunakan default aktif
                        $currentTaId = $data['value'] ?? $activeTaId;

                        if (!$currentTaId) return $query;

                        return $query
                            ->whereHas('kelasSiswa', fn($q) => $q->where('tahun_ajaran_id', $currentTaId))
                            ->with([
                                'kelasSiswa' => fn($q) => $q->where('tahun_ajaran_id', $currentTaId)->with('siswa.user'),
                                'waliKelas' => fn($q) => $q->where('tahun_ajaran_id', $currentTaId)->with('guru.user'),
                            ])
                            ->withCount([
                                'kelasSiswa as jumlah_siswa' => fn($q) => $q->where('tahun_ajaran_id', $currentTaId)->where('status', 'aktif')
                            ]);
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (! ($data['value'] ?? null)) return null;
                        $target = TahunAjaran::find($data['value']);
                        return $target ? "Tahun ajaran: {$target->nama_semester}" : null;
                    })
            ])
            ->recordActions([
                // Action::make('assignWaliKelas')
                //     ->label('Set Wali Kelas')
                //     ->icon(Heroicon::OutlinedAcademicCap)
                //     ->color('warning')
                //     ->schema([
                //         Select::make('guru_id')
                //             ->label('Pilih Guru')
                //             ->options(
                //                 fn() => Guru::query()
                //                     ->join('users', 'gurus.user_id', '=', 'users.id')
                //                     ->pluck('users.name', 'gurus.id')
                //             )
                //             ->searchable()
                //             ->preload()
                //             ->required()
                //     ])
                //     ->mountUsing(function ($form, $record) use ($activeTaId) {
                //         $currentTaId =
                //             request()->input('tableFilters.tahun_ajaran.value')
                //             ?? $activeTaId;

                //         if (! $currentTaId) return;

                //         $wali = WaliKelas::where('kelas_id', $record->id)
                //             ->where('tahun_ajaran_id', $currentTaId)
                //             ->first();

                //         if ($wali) {
                //             $form->fill([
                //                 'guru_id' => $wali->guru_id,
                //             ]);
                //         }
                //     })
                //     ->action(function (array $data, $record): void {
                //         $currentTaId = request()->input('tableFilters.tahun_ajaran.value')
                //             ?? TahunAjaran::where('is_active', true)->value('id');

                //         if (! $currentTaId) {
                //             Notification::make()
                //                 ->title('Gagal')
                //                 ->body('Tahun ajaran aktif tidak ditemukan.')
                //                 ->danger()
                //                 ->send();
                //             return;
                //         }

                //         try {
                //             DB::transaction(function () use ($data, $record, $currentTaId) {
                //                 // 1. Hapus wali kelas lama di kelas & TA yang sama (jika ada)
                //                 $record->waliKelas()
                //                     ->wherePivot('tahun_ajaran_id', $currentTaId)
                //                     ->detach();

                //                 // 2. Pasangkan wali kelas baru
                //                 $record->waliKelas()->attach($data['guru_id'], [
                //                     'id' => (string) Str::uuid(),
                //                     'tahun_ajaran_id' => $currentTaId,
                //                     'is_active' => true,
                //                 ]);
                //             });

                //             Notification::make()
                //                 ->title('Berhasil')
                //                 ->body('Wali kelas telah diperbarui untuk tahun ajaran terkait.')
                //                 ->success()
                //                 ->send();
                //         } catch (\Exception $e) {
                //             Notification::make()
                //                 ->title('Terjadi Kesalahan')
                //                 ->body($e->getMessage())
                //                 ->danger()
                //                 ->send();
                //         }
                //     }),

                ViewAction::make()
                    ->label('Kelola Siswa')
                    ->icon(Heroicon::OutlinedUserGroup)
                    ->color('primary'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('nama', 'asc');
    }
}
