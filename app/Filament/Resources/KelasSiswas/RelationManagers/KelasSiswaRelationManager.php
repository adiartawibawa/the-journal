<?php

namespace App\Filament\Resources\KelasSiswas\RelationManagers;

use App\Models\Kelas;
use App\Models\TahunAjaran;
use Filament\Actions\AssociateAction;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KelasSiswaRelationManager extends RelationManager
{
    protected static string $relationship = 'kelasSiswa';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('siswa_id')
                    ->relationship('siswa.user', 'name') // Mengambil nama dari relasi User
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Nama Siswa')
                    ->columnSpanFull(),

                Select::make('status')
                    ->options([
                        'aktif' => 'Aktif',
                        'lulus' => 'Lulus',
                        'tinggal_kelas' => 'Tinggal Kelas',
                        'pindah' => 'Pindah',
                        'keluar' => 'Keluar',
                    ])
                    ->required()
                    ->default('aktif'),

                DatePicker::make('tanggal_mulai')
                    ->default(now())
                    ->required(),

                DatePicker::make('tanggal_selesai'),

                Textarea::make('keterangan')
                    ->columnSpanFull(),

                // Hidden field untuk mengunci Tahun Ajaran saat input data baru
                Hidden::make('tahun_ajaran_id')
                    ->default(fn() => TahunAjaran::where('is_active', true)->value('id')),

            ]);
    }

    public function table(Table $table): Table
    {
        // Ambil ID Tahun Ajaran Aktif untuk filtering
        $activeTahunAjaranId = TahunAjaran::where('is_active', true)->value('id');

        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->where('tahun_ajaran_id', $activeTahunAjaranId))
            ->columns([
                TextColumn::make('siswa.nisn')
                    ->label('NISN')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('siswa.user.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'aktif' => 'success',
                        'lulus' => 'info',
                        'tinggal_kelas', 'keluar' => 'danger',
                        default => 'warning',
                    }),

                TextColumn::make('tanggal_mulai')
                    ->date('d/m/Y')
                    ->label('Mulai'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Tambah Siswa ke Kelas')
                    ->preloadRecordSelect()
                    ->form(fn(AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Hidden::make('tahun_ajaran_id')
                            ->default(fn() => TahunAjaran::where('is_active', true)->value('id')),
                        Select::make('status')
                            ->options(['aktif' => 'Aktif'])
                            ->default('aktif')
                            ->required(),
                        DatePicker::make('tanggal_mulai')
                            ->default(now())
                            ->required(),
                    ])
                    ->mutateFormDataUsing(function (array $data) {
                        $data['id'] = (string) Str::uuid();
                        return $data;
                    }),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                // DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('prosesKenaikanKelas')
                        ->label('Proses Kenaikan/Kelulusan')
                        ->icon('heroicon-o-arrow-trending-up')
                        ->color('success')
                        ->form([
                            Select::make('tahun_ajaran_baru_id')
                                ->label('Tahun Ajaran Baru')
                                ->options(fn() => TahunAjaran::where('is_active', false)->pluck('nama', 'id'))
                                ->required()
                                ->helperText('Pilih tahun ajaran tujuan.'),

                            Select::make('target_kelas_id')
                                ->label('Target Kelas Baru')
                                ->options(fn() => Kelas::all()->pluck('nama', 'id'))
                                ->required()
                                ->helperText('Pilih kelas tujuan untuk siswa yang terpilih.'),

                            Select::make('status_akhir')
                                ->label('Status Riwayat Lama')
                                ->options([
                                    'lulus' => 'Lulus',
                                    'naik_kelas' => 'Naik Kelas',
                                ])
                                ->required()
                                ->default('lulus'),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            try {
                                DB::transaction(function () use ($records, $data) {
                                    $successCount = 0;
                                    $failCount = 0;

                                    foreach ($records as $record) {
                                        try {
                                            // Memanggil logic naikKelas dari model KelasSiswa
                                            $record->naikKelas(
                                                $data['tahun_ajaran_baru_id'],
                                                $data['target_kelas_id']
                                            );

                                            // Opsional: Update status record lama sesuai input form
                                            $record->update(['status' => $data['status_akhir']]);

                                            $successCount++;
                                        } catch (\Exception $e) {
                                            $failCount++;
                                        }
                                    }

                                    Notification::make()
                                        ->title('Proses Selesai')
                                        ->success()
                                        ->body("Berhasil memproses {$successCount} siswa. Gagal: {$failCount}.")
                                        ->send();
                                });
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Kenaikan Kelas Gagal')
                                    ->body('Terjadi kesalahan, tidak ada data yang diubah: ' . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),

                    BulkAction::make('prosesTinggalKelas')
                        ->label('Proses Tinggal Kelas')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->form([
                            Select::make('tahun_ajaran_baru_id')
                                ->label('Tahun Ajaran Baru (Tahun Mengulang)')
                                ->options(fn() => \App\Models\TahunAjaran::where('is_active', false)->pluck('nama', 'id'))
                                ->required(),

                            Textarea::make('keterangan')
                                ->label('Alasan Tinggal Kelas')
                                ->placeholder('Contoh: Nilai tidak memenuhi syarat kelulusan minimal.')
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            try {
                                DB::transaction(function () use ($records, $data) {
                                    foreach ($records as $record) {
                                        // Menggunakan method tinggalKelas yang sudah ada di model KelasSiswa.php
                                        $record->tinggalKelas(
                                            $data['tahun_ajaran_baru_id'],
                                            $data['keterangan']
                                        );
                                    }
                                });

                                Notification::make()
                                    ->title('Berhasil')
                                    ->body('Status siswa berhasil diperbarui menjadi tinggal kelas.')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Gagal')
                                    ->body('Terjadi kesalahan: ' . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
