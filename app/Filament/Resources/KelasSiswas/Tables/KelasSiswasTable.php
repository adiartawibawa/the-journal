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
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class KelasSiswasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('siswa.user.name')
                    ->label('Nama Siswa')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('siswa.user', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->join('siswas', 'kelas_siswa.siswa_id', '=', 'siswas.id')
                            ->join('users', 'siswas.user_id', '=', 'users.id')
                            ->orderBy('users.name', $direction)
                            ->select('kelas_siswa.*');
                    }),

                TextColumn::make('kelas.nama')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kelas.tingkat')
                    ->label('Tingkat')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn($state) => "Kelas {$state}"),

                TextColumn::make('tahunAjaran.nama')
                    ->label('Tahun Ajaran')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => ucfirst(str_replace('_', ' ', $state)))
                    ->color(fn(string $state): string => match ($state) {
                        'aktif' => 'success',
                        'pindah' => 'warning',
                        'lulus' => 'info',
                        'dropout' => 'danger',
                        'tinggal_kelas' => 'gray',
                        default => 'secondary',
                    }),

                TextColumn::make('periode')
                    ->label('Periode')
                    ->toggleable(),

                TextColumn::make('tanggal_mulai')
                    ->label('Tgl Mulai')
                    ->date('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('tanggal_selesai')
                    ->label('Tgl Selesai')
                    ->date('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            // ->groups([
            //     Group::make('kelas_id')
            //         ->label('Kelas')
            //         ->getTitleFromRecordUsing(
            //             fn($record) =>
            //             "{$record->kelas->nama} - Tingkat {$record->kelas->tingkat}"
            //         ),
            // ])
            // ->defaultGroup('kelas_id')
            ->filters([
                SelectFilter::make('tahun_ajaran_id')
                    ->label('Tahun Ajaran')
                    ->options(function () {
                        return TahunAjaran::query()
                            ->orderBy('tanggal_awal', 'desc')
                            ->get()
                            ->mapWithKeys(function ($tahunAjaran) {
                                $semesterLabel = $tahunAjaran->semester === '1' ? 'Ganjil' : 'Genap';
                                return [
                                    $tahunAjaran->id => "{$tahunAjaran->nama} - Semester {$semesterLabel}",
                                ];
                            })
                            ->toArray();
                    })
                    ->default(fn() => TahunAjaran::where('is_active', true)->value('id'))
                    ->query(function (Builder $query, array $data) {
                        if (filled($data['value'])) {
                            $query->where('tahun_ajaran_id', $data['value']);
                        }
                    })
                    ->searchable()
                    ->preload(),

                SelectFilter::make('kelas_id')
                    ->label('Kelas')
                    ->relationship('kelas', 'nama')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'aktif' => 'Aktif',
                        'pindah' => 'Pindah',
                        'lulus' => 'Lulus',
                        'dropout' => 'Dropout',
                        'tinggal_kelas' => 'Tinggal Kelas',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('naikKelas')
                    ->label('Naik Kelas')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->color('success')
                    ->visible(fn(KelasSiswa $record): bool => $record->status === 'aktif')
                    ->requiresConfirmation()
                    ->modalHeading('Naik Kelas Siswa')
                    ->modalDescription('Pilih tahun ajaran dan kelas tujuan untuk kenaikan kelas siswa ini.')
                    ->form([
                        Select::make('tahun_ajaran_id')
                            ->label('Tahun Ajaran Baru')
                            ->options(function () {
                                return TahunAjaran::all()->mapWithKeys(function ($tahunAjaran) {
                                    $semesterLabel = $tahunAjaran->semester == '1' ? 'Ganjil' : 'Genap';
                                    return [$tahunAjaran->id => $tahunAjaran->nama . ' - Semester ' . $semesterLabel];
                                });
                            })
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn(callable $set) => $set('kelas_id', null)),

                        Select::make('kelas_id')
                            ->label('Kelas Tujuan')
                            ->options(function (callable $get) {
                                $tahunAjaranId = $get('tahun_ajaran_id');
                                if (!$tahunAjaranId) {
                                    return [];
                                }

                                return Kelas::where('is_active', true)
                                    ->get()
                                    ->mapWithKeys(function ($kelas) use ($tahunAjaranId) {
                                        $kapasitas = $kelas->kapasitas;
                                        $terisi = $kelas->getJumlahSiswaAktifAttribute($tahunAjaranId);

                                        // Filter hanya kelas yang masih punya kuota
                                        if ($terisi < $kapasitas) {
                                            $sisa = $kapasitas - $terisi;
                                            return [
                                                $kelas->id => "{$kelas->nama} (Tingkat {$kelas->tingkat}) - Terisi: {$terisi}/{$kapasitas} (Sisa: {$sisa})"
                                            ];
                                        }

                                        return [];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->required()
                            ->helperText(function (callable $get) {
                                $kelasId = $get('kelas_id');
                                if (!$kelasId) {
                                    return 'Pilih kelas tujuan';
                                }

                                $kelas = Kelas::find($kelasId);
                                $tahunAjaranId = $get('tahun_ajaran_id');

                                if ($kelas && $tahunAjaranId) {
                                    $terisi = $kelas->getJumlahSiswaAktifAttribute($tahunAjaranId);
                                    $sisa = $kelas->kapasitas - $terisi;
                                    return "Sisa kapasitas: {$sisa} dari {$kelas->kapasitas}";
                                }

                                return null;
                            }),

                        Textarea::make('keterangan')
                            ->label('Keterangan (Opsional)')
                            ->rows(2),
                    ])
                    ->action(function (KelasSiswa $record, array $data): void {
                        try {
                            DB::beginTransaction();

                            $record->naikKelas(
                                $data['tahun_ajaran_id'],
                                $data['kelas_id'],
                                $data['keterangan'] ?? null
                            );

                            DB::commit();

                            Notification::make()
                                ->title('Sukses!')
                                ->body('Siswa berhasil dinaikkan ke kelas berikutnya.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            DB::rollBack();

                            Notification::make()
                                ->title('Error!')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('tinggalKelas')
                    ->label('Tinggal Kelas')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->visible(fn(KelasSiswa $record): bool => $record->status === 'aktif')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Tinggal Kelas')
                    ->modalDescription('Apakah Anda yakin siswa ini tinggal kelas? Siswa akan mengulang di kelas yang sama pada tahun ajaran baru.')
                    ->form([
                        Select::make('tahun_ajaran_id')
                            ->label('Tahun Ajaran Baru')
                            ->options(function () {
                                return TahunAjaran::all()->mapWithKeys(function ($tahunAjaran) {
                                    $semesterLabel = $tahunAjaran->semester == '1' ? 'Ganjil' : 'Genap';
                                    return [$tahunAjaran->id => $tahunAjaran->nama . ' - Semester ' . $semesterLabel];
                                });
                            })
                            ->searchable()
                            ->required()
                            ->helperText('Pilih tahun ajaran dimana siswa akan mengulang'),

                        Textarea::make('keterangan')
                            ->label('Alasan / Keterangan')
                            ->rows(3)
                            ->required()
                            ->placeholder('Contoh: Tidak memenuhi KKM, sering tidak masuk, dll.'),
                    ])
                    ->action(function (KelasSiswa $record, array $data): void {
                        try {
                            DB::beginTransaction();

                            // Validate kapasitas kelas
                            $kelas = $record->kelas;
                            $tahunAjaranBaruId = $data['tahun_ajaran_id'];
                            $currentCount = $kelas->getJumlahSiswaAktifAttribute($tahunAjaranBaruId);

                            if ($currentCount >= $kelas->kapasitas) {
                                throw new \Exception("Kapasitas kelas {$kelas->nama} untuk tahun ajaran baru sudah penuh.");
                            }

                            $record->tinggalKelas(
                                $tahunAjaranBaruId,
                                $data['keterangan']
                            );

                            DB::commit();

                            Notification::make()
                                ->title('Sukses!')
                                ->body('Status siswa berhasil diubah menjadi tinggal kelas.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            DB::rollBack();

                            Notification::make()
                                ->title('Error!')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

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
