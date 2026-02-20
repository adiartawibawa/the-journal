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
use Filament\Schemas\Components\Utilities\Get;
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
                        'mutasi' => 'Mutasi',
                        'keluar' => 'Keluar',
                    ])->required(),

                DatePicker::make('tanggal_masuk')
                    ->default(now())->required(),

                DatePicker::make('tanggal_keluar'),

                Select::make('hasil_akhir')
                    ->options([
                        'naik_kelas' => 'Naik Kelas',
                        'tinggal_kelas' => 'Tinggal Kelas',
                        'lulus' => 'Lulus',
                    ])->placeholder('Belum ada hasil'),

                Textarea::make('catatan_internal')->columnSpanFull(),

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
                        'mutasi' => 'warning',
                        'keluar' => 'danger',
                    }),

                TextColumn::make('hasil_akhir')
                    ->badge()
                    ->placeholder('-'),

                TextColumn::make('tanggal_masuk')
                    ->date('d/m/Y')->label('Masuk'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Tambah Siswa ke Kelas')
                    ->preloadRecordSelect()
                    ->schema(fn(AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Hidden::make('tahun_ajaran_id')
                            ->default(fn() => TahunAjaran::where('is_active', true)->value('id')),
                        Select::make('status')
                            ->options(['aktif' => 'Aktif'])
                            ->default('aktif')
                            ->required(),
                        DatePicker::make('tanggal_masuk')->default(now())->required(),
                    ])
                    ->mutateFormDataUsing(function (array $data) {
                        $data['id'] = (string) Str::uuid();
                        return $data;
                    }),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('lanjutSemester')
                        ->label('Lanjut Semester')
                        ->icon('heroicon-o-arrow-path')
                        ->color('success')
                        ->schema([
                            Select::make('semester_baru_id')
                                ->label('Target Semester (Genap)')
                                ->options(fn() => TahunAjaran::where('is_active', false)->get()->pluck('nama_semester', 'id'))
                                ->required(),
                            DatePicker::make('tanggal_masuk')
                                ->label('Tanggal Mulai Semester Baru')
                                ->default(now())
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            try {
                                $processedCount = 0;
                                DB::transaction(function () use ($records, $data, &$processedCount) {
                                    foreach ($records as $record) {
                                        $result = $record->lanjutSemester($data['semester_baru_id']);
                                        if ($result) {
                                            $processedCount++;
                                        }
                                    }
                                });

                                if ($processedCount > 0) {
                                    Notification::make()
                                        ->title('Berhasil')
                                        ->body("{$processedCount} siswa berhasil dilanjutkan ke semester baru.")
                                        ->success()
                                        ->send();
                                } else {
                                    Notification::make()
                                        ->title('Info')
                                        ->body("Siswa sudah terdaftar di semester tujuan atau tidak ada data yang diproses.")
                                        ->info()
                                        ->send();
                                }
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Gagal')
                                    ->body('Terjadi kesalahan: ' . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),

                    BulkAction::make('prosesKenaikanKelas')
                        ->label('Proses Kenaikan/Kelulusan')
                        ->icon('heroicon-o-arrow-trending-up')
                        ->color('success')
                        ->visible(fn() => TahunAjaran::where('is_active', true)->first()?->isSemesterGenap())
                        ->schema([
                            Select::make('status_akhir')
                                ->label('Hasil Akhir di Kelas Ini')
                                ->options([
                                    'naik_kelas' => 'Naik Kelas',
                                    'lulus' => 'Lulus (Alumni)',
                                ])
                                ->required()
                                ->live()
                                ->default('naik_kelas'),

                            Select::make('tahun_ajaran_baru_id')
                                ->label('Tahun Ajaran Baru')
                                ->options(fn() => TahunAjaran::where('is_active', false)->get()->pluck('nama_semester', 'id'))
                                ->required()
                                ->visible(fn(Get $get): bool => $get('status_akhir') === 'naik_kelas')
                                ->helperText('Pilih tahun ajaran tujuan.'),

                            Select::make('target_kelas_id')
                                ->label('Target Kelas Baru')
                                ->options(fn() => Kelas::all()->pluck('nama', 'id'))
                                ->required()
                                ->visible(fn(Get $get): bool => $get('status_akhir') === 'naik_kelas') // Hanya jika naik kelas
                                ->helperText('Pilih kelas tujuan untuk siswa yang terpilih.'),

                            Textarea::make('catatan_internal')
                                ->label('Catatan Kenaikan/Kelulusan')
                                ->placeholder('Opsional: Masukkan catatan jika diperlukan.'),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            try {
                                DB::transaction(function () use ($records, $data) {
                                    $successCount = 0;

                                    foreach ($records as $record) {
                                        if ($data['status_akhir'] === 'naik_kelas') {
                                            // Memanggil logic naikKelas yang sudah disesuaikan di Model
                                            $record->naikKelas(
                                                $data['tahun_ajaran_baru_id'],
                                                $data['target_kelas_id']
                                            );
                                        } else {
                                            // Logic untuk kelulusan (Alumni)
                                            $record->update([
                                                'status' => 'keluar',
                                                'hasil_akhir' => 'lulus',
                                                'tanggal_keluar' => now(),
                                                'catatan_internal' => $data['catatan_internal'] ?? 'Dinyatakan Lulus.',
                                            ]);
                                        }
                                        $successCount++;
                                    }

                                    Notification::make()
                                        ->title('Proses Berhasil')
                                        ->success()
                                        ->body("Berhasil memproses {$successCount} siswa.")
                                        ->send();
                                });
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Proses Gagal')
                                    ->body('Terjadi kesalahan: ' . $e->getMessage())
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
                        ->visible(fn() => TahunAjaran::where('is_active', true)->first()?->isSemesterGenap())
                        ->schema([
                            Select::make('tahun_ajaran_baru_id')
                                ->label('Tahun Ajaran Baru (Tahun Mengulang)')
                                ->options(fn() => TahunAjaran::where('is_active', false)->get()->pluck('nama_semester', 'id'))
                                ->required()
                                ->helperText('Pilih tahun di mana siswa akan mengulang.'),

                            Textarea::make('catatan_internal')
                                ->label('Alasan Tinggal Kelas')
                                ->placeholder('Contoh: Absensi kurang dari 75% atau nilai di bawah KKM.')
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            try {
                                DB::transaction(function () use ($records, $data) {
                                    foreach ($records as $record) {
                                        // Memanggil method tinggalKelas di model yang sudah diperbarui
                                        $record->tinggalKelas(
                                            $data['tahun_ajaran_baru_id'],
                                            $data['catatan_internal']
                                        );
                                    }
                                });

                                Notification::make()
                                    ->title('Berhasil')
                                    ->body('Siswa berhasil tercatat tinggal kelas dan didaftarkan ulang pada tahun ajaran berikutnya.')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Gagal')
                                    ->body('Kesalahan sistem: ' . $e->getMessage())
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
