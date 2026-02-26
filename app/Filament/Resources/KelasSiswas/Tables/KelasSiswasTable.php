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
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class KelasSiswasTable
{
    public static function configure(Table $table): Table
    {
        $activeTaId = TahunAjaran::getActive()->id;

        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = Auth::user();

                // Jika user adalah teacher, batasi hanya kelas perwaliannya saja
                if ($user->hasRole('teacher')) {
                    $guruId = $user->profileGuru?->id;

                    return $query->whereHas('waliKelas', function ($q) use ($guruId) {
                        $q->where('guru_id', $guruId);
                        // tambahkan filter agar hanya perwalian di tahun ajaran aktif
                        $q->where('is_active', true);
                    });
                }

                return $query;
            })
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
                        $wali = $record->waliKelas->first();
                        return $wali ? $wali->guru->user->name : '- Belum Ada -';
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
                    ->badge(),

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
                                'waliKelas' => fn($q) => $q->where('tahun_ajaran_id', $currentTaId)->with(['guru.user']),
                            ])
                            ->withCount([
                                'kelasSiswa as jumlah_siswa_aktif' => fn($q) => $q->where('tahun_ajaran_id', $currentTaId)->where('status', 'aktif')
                            ]);
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (! ($data['value'] ?? null)) return null;
                        $target = TahunAjaran::find($data['value']);
                        return $target ? "Tahun ajaran: {$target->nama_semester}" : null;
                    })
            ])
            ->recordActions([
                Action::make('assignWaliKelas')
                    ->label('Set Wali Kelas')
                    ->icon(Heroicon::OutlinedAcademicCap)
                    ->color('warning')
                    ->visible(fn() => Auth::user()->hasRole(['super_admin', 'admin']))
                    ->schema([
                        Select::make('guru_id')
                            ->label('Pilih Guru')
                            ->options(function () {
                                // Ambil ID Tahun Ajaran (Logika disamakan dengan di action)
                                $currentTaId = self::getFilteredTahunAjaranId();

                                // Ambil daftar guru_id yang SUDAH menjadi wali kelas di TA tersebut
                                $assignedGuruIds = WaliKelas::where('tahun_ajaran_id', $currentTaId)
                                    ->pluck('guru_id')
                                    ->toArray();

                                // Tampilkan guru yang belum ter-assign
                                return Guru::whereNotIn('id', $assignedGuruIds)
                                    ->with('user:id,name') // Hanya ambil kolom yang diperlukan
                                    ->get()
                                    ->pluck('user.name', 'id');
                            })
                            ->searchable()
                            ->preload(false)
                            ->getSearchResultsUsing(function (string $search) {
                                $currentTaId = self::getFilteredTahunAjaranId();

                                $assignedGuruIds = WaliKelas::where('tahun_ajaran_id', $currentTaId)
                                    ->pluck('guru_id');

                                return Guru::whereNotIn('id', $assignedGuruIds)
                                    ->whereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"))
                                    ->with('user:id,name')
                                    ->limit(50) // Batasi hasil pencarian
                                    ->get()
                                    ->pluck('user.name', 'id');
                            })
                            ->required()
                    ])
                    ->action(function (array $data, Model $record): void {
                        $currentTaId = self::getFilteredTahunAjaranId();

                        if (! $currentTaId) {
                            Notification::make()
                                ->title('Gagal')
                                ->body('Tahun ajaran aktif tidak ditemukan.')
                                ->danger()
                                ->send();
                            return;
                        }

                        try {
                            WaliKelas::updateOrCreate(
                                ['kelas_id' => $record->id, 'tahun_ajaran_id' => $currentTaId],
                                ['guru_id' => $data['guru_id'], 'is_active' => true]
                            );

                            Notification::make()
                                ->title('Berhasil')
                                ->body('Wali kelas telah diperbarui untuk tahun ajaran terkait.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Terjadi Kesalahan')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                ViewAction::make()
                    ->label(fn(): bool => !Auth::user()->hasRole(['super_admin', 'admin']) ? 'Lihat Siswa' : 'Kelola Siswa')
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

    private static function getFilteredTahunAjaranId()
    {
        return request()->input('tableFilters.tahun_ajaran.value') ?? TahunAjaran::getActive()->id;
    }
}
