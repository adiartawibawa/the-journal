<?php

namespace App\Filament\Resources\KelasSiswas\Tables;

use App\Models\Kelas;
use App\Models\KelasSiswa;
use App\Models\TahunAjaran;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class KelasSiswasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('siswa.user.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('siswa.nisn')
                    ->label('NISN')
                    ->searchable(),

                TextColumn::make('kelas.nama')
                    ->label('Kelas')
                    ->description(fn($record) => 'Tingkat ' . $record->kelas?->tingkat)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tahunAjaran.nama')
                    ->label('Tahun Ajaran')
                    ->badge()
                    ->color(fn($record) => $record->tahunAjaran?->is_active ? 'success' : 'gray')
                    ->description(fn($record) => 'Semester ' . $record->tahunAjaran?->semester)
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'aktif' => 'success',
                        'pindah' => 'warning',
                        'lulus' => 'info',
                        'dropout' => 'danger',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'aktif' => 'heroicon-o-check-circle',
                        'pindah' => 'heroicon-o-arrows-right-left',
                        'lulus' => 'heroicon-o-academic-cap',
                        'dropout' => 'heroicon-o-x-circle',
                    }),

                TextColumn::make('periode')
                    ->label('Periode')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tahun_ajaran_id')
                    ->label('Tahun Ajaran')
                    ->options(TahunAjaran::all()->pluck('nama', 'id'))
                    ->default(function () {
                        return TahunAjaran::where('is_active', true)->first()?->id;
                    }),

                SelectFilter::make('kelas_id')
                    ->label('Kelas')
                    ->options(function () {
                        return Kelas::active()
                            ->orderBy('tingkat')
                            ->orderBy('nama')
                            ->get()
                            ->mapWithKeys(function ($kelas) {
                                return [$kelas->id => $kelas->nama . ' (Tkt ' . $kelas->tingkat . ')'];
                            });
                    })
                    ->searchable(),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'aktif' => 'Aktif',
                        'pindah' => 'Pindah',
                        'lulus' => 'Lulus',
                        'dropout' => 'Drop Out',
                    ]),

                Filter::make('kelas_aktif')
                    ->label('Kelas Aktif')
                    ->query(fn(Builder $query): Builder => $query->where('status', 'aktif'))
                    ->toggle(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('naikKelas')
                    ->label('Naik Kelas')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Naik Kelas Siswa')
                    ->modalDescription('Apakah Anda yakin ingin menaikkan kelas siswa ini?')
                    ->form([
                        Select::make('tahun_ajaran_baru')
                            ->label('Tahun Ajaran Baru')
                            ->options(TahunAjaran::where('is_active', false)
                                ->orWhere('id', TahunAjaran::where('is_active', true)->first()?->id)
                                ->orderBy('tanggal_awal', 'desc')
                                ->get()
                                ->pluck('nama', 'id'))
                            ->required()
                            ->searchable(),

                        Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->rows(2)
                            ->placeholder('Contoh: Naik kelas dari X IPA 1 ke XI IPA 1'),
                    ])
                    ->action(function (KelasSiswa $record, array $data) {
                        try {
                            $keterangan = $data['keterangan'] ?? null;
                            $record->naikKelas($data['tahun_ajaran_baru'], $keterangan);

                            Notification::make()
                                ->success()
                                ->title('Berhasil')
                                ->body('Siswa berhasil dinaikkan ke kelas berikutnya.')
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Gagal')
                                ->body($e->getMessage())
                                ->send();
                        }
                    })
                    ->visible(fn(KelasSiswa $record): bool => $record->status === 'aktif'),

                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('naikKelasMassal')
                        ->label('Naik Kelas Massal')
                        ->icon('heroicon-o-arrow-up-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Naik Kelas Massal')
                        ->modalDescription('Pilih tahun ajaran baru untuk menaikkan kelas siswa-siswa yang dipilih.')
                        ->form([
                            Select::make('tahun_ajaran_baru')
                                ->label('Tahun Ajaran Baru')
                                ->options(TahunAjaran::where('is_active', false)
                                    ->orWhere('id', TahunAjaran::where('is_active', true)->first()?->id)
                                    ->orderBy('tanggal_awal', 'desc')
                                    ->get()
                                    ->pluck('nama', 'id'))
                                ->required()
                                ->searchable()
                                ->helperText('Siswa akan dipindahkan ke kelas dengan tingkat selanjutnya'),

                            Textarea::make('keterangan')
                                ->label('Keterangan')
                                ->rows(2)
                                ->placeholder('Keterangan untuk semua siswa yang dinaikkan kelas'),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $berhasil = 0;
                            $gagal = 0;

                            foreach ($records as $record) {
                                if ($record->status !== 'aktif') {
                                    $gagal++;
                                    continue;
                                }

                                try {
                                    $keterangan = $data['keterangan'] ?? null;
                                    $record->naikKelas($data['tahun_ajaran_baru'], $keterangan);
                                    $berhasil++;
                                } catch (\Exception $e) {
                                    $gagal++;
                                }
                            }

                            Notification::make()
                                ->success()
                                ->title('Proses Naik Kelas Selesai')
                                ->body("Berhasil: {$berhasil} siswa, Gagal: {$gagal} siswa")
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('ubahStatus')
                        ->label('Ubah Status')
                        ->icon('heroicon-o-pencil-square')
                        ->form([
                            Select::make('status')
                                ->label('Status Baru')
                                ->options([
                                    'aktif' => 'Aktif',
                                    'pindah' => 'Pindah',
                                    'lulus' => 'Lulus',
                                    'dropout' => 'Drop Out',
                                ])
                                ->required(),

                            Textarea::make('keterangan')
                                ->label('Keterangan')
                                ->rows(2),
                        ])
                        ->action(function (Collection $records, array $data) {
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => $data['status'],
                                    'keterangan' => $data['keterangan'] ?? $record->keterangan,
                                    'tanggal_selesai' => $data['status'] !== 'aktif' ? now() : $record->tanggal_selesai,
                                ]);
                            }

                            Notification::make()
                                ->success()
                                ->title('Status Berhasil Diubah')
                                ->body(count($records) . ' siswa telah diubah statusnya.')
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
