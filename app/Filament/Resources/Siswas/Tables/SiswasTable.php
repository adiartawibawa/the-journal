<?php

namespace App\Filament\Resources\Siswas\Tables;

use App\Models\Kelas;
use App\Models\KelasSiswa;
use App\Models\TahunAjaran;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SiswasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nisn')
                    ->label('NISN')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tanggal_lahir')
                    ->date()
                    ->searchable(),

                TextColumn::make('tempat_lahir')
                    ->searchable(),

                TextColumn::make('nama_ayah')
                    ->searchable(),

                TextColumn::make('nama_ibu')
                    ->searchable(),

                TextColumn::make('pekerjaan_orang_tua')
                    ->searchable(),

                TextColumn::make('alamat_orang_tua')
                    ->searchable(),

                TextColumn::make('no_telp_orang_tua')
                    ->searchable(),

                TextColumn::make('status_terakhir')
                    ->getStateUsing(fn($record) => $record->getKelasAktif()?->status ?? 'Belum Terdaftar')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'aktif' => 'success',
                        'lulus' => 'gray',
                        default => 'warning',
                    }),

                IconColumn::make('is_active')
                    ->label('Status Siswa')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('belum_punya_kelas')
                    ->label('Siswa Belum Ada Kelas')
                    ->query(fn(Builder $query) => $query->whereDoesntHave('kelasSiswa')),

                TernaryFilter::make('is_active')
                    ->label('Status Siswa Aktif'),

                TernaryFilter::make('user.is_active')
                    ->label('Status Akun Aktif'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('assignKelasBaru')
                        ->label('Plotting Kelas Siswa Baru')
                        ->icon('heroicon-o-plus-circle')
                        ->color('info')
                        ->form([
                            Select::make('tahun_ajaran_id')
                                ->label('Tahun Ajaran Aktif')
                                ->options(fn() => TahunAjaran::where('is_active', true)->pluck('nama', 'id'))
                                ->default(fn() => TahunAjaran::where('is_active', true)->value('id'))
                                ->required(),

                            Select::make('kelas_id')
                                ->label('Target Kelas')
                                ->options(fn() => Kelas::active()->pluck('nama', 'id'))
                                ->required()
                                ->searchable(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            try {
                                // Memulai Transaksi
                                DB::transaction(function () use ($records, $data) {
                                    $targetKelas = Kelas::find($data['kelas_id']);
                                    $tahunAjaranId = $data['tahun_ajaran_id'];

                                    $successCount = 0;
                                    $skippedCount = 0;

                                    foreach ($records as $siswa) {
                                        // Validasi: Apakah siswa sudah punya kelas di tahun ajaran tersebut?
                                        $exists = KelasSiswa::where('siswa_id', $siswa->id)
                                            ->where('tahun_ajaran_id', $tahunAjaranId)
                                            ->exists();

                                        if ($exists) {
                                            $skippedCount++;
                                            continue;
                                        }

                                        // Cek Kapasitas Kelas
                                        if ($targetKelas->getJumlahSiswaAktifAttribute($tahunAjaranId) < $targetKelas->kapasitas) {
                                            KelasSiswa::create([
                                                'tahun_ajaran_id' => $tahunAjaranId,
                                                'kelas_id' => $targetKelas->id,
                                                'siswa_id' => $siswa->id,
                                                'status' => 'aktif',
                                                'tanggal_mulai' => now(),
                                            ]);
                                            $successCount++;
                                        } else {
                                            Notification::make()
                                                ->title('Kapasitas Penuh')
                                                ->body("Kelas {$targetKelas->nama} sudah mencapai batas kapasitas.")
                                                ->danger()
                                                ->send();
                                            break;
                                        }
                                    }
                                    Notification::make()
                                        ->title('Proses Plotting Selesai')
                                        ->success()
                                        ->body("Berhasil memasukkan {$successCount} siswa ke kelas. (Dilewati: {$skippedCount})")
                                        ->send();
                                });
                            } catch (\Exception $e) {
                                // Jika ada throw Exception di dalam transaction, otomatis rollback
                                Notification::make()
                                    ->title('Proses Dibatalkan')
                                    ->body('Gagal memproses data: ' . $e->getMessage())
                                    ->danger()
                                    ->persistent()
                                    ->send();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
