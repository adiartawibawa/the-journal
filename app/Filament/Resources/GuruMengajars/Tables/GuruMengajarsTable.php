<?php

namespace App\Filament\Resources\GuruMengajars\Tables;

use App\Models\TahunAjaran;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GuruMengajarsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('guru.name')
                    ->label('Nama Guru')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('guru.nuptk')
                    ->label('NUPTK')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('mapel.nama')
                    ->label('Mata Pelajaran')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kelas.nama')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tahunAjaran.nama')
                    ->label('Tahun Ajaran')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        $tahunAjaran = $record->tahunAjaran;
                        if ($tahunAjaran && $tahunAjaran->is_active) {
                            return $state . ' (Aktif)';
                        }
                        return $state;
                    })
                    ->color(function ($record) {
                        if ($record->tahunAjaran && $record->tahunAjaran->is_active) {
                            return 'success';
                        }
                        return 'gray';
                    })
                    ->badge(),

                TextColumn::make('kkm')
                    ->label('KKM')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('jam_per_minggu')
                    ->label('Jam/Minggu')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diupdate')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tahun_ajaran_id')
                    ->label('Tahun Ajaran')
                    ->relationship('tahunAjaran', 'nama')
                    ->searchable()
                    ->preload()
                    ->options(function () {
                        return TahunAjaran::where('is_active', true)
                            ->orWhereHas('guruMengajar')
                            ->pluck('nama', 'id')
                            ->toArray();
                    })
                    ->default(function () {
                        $activeTahunAjaran = TahunAjaran::where('is_active', true)->first();
                        return $activeTahunAjaran?->id;
                    }),

                SelectFilter::make('kelas_id')
                    ->label('Kelas')
                    ->relationship('kelas', 'nama')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('mapel_id')
                    ->label('Mata Pelajaran')
                    ->relationship('mapel', 'nama')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        true => 'Aktif',
                        false => 'Tidak Aktif',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])->defaultSort('created_at', 'desc');
    }
}
