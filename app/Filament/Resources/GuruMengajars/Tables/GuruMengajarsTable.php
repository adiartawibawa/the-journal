<?php

namespace App\Filament\Resources\GuruMengajars\Tables;

use App\Models\GuruMengajar;
use App\Models\TahunAjaran;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class GuruMengajarsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->groups([
                Group::make('guru.user.name')
                    ->label('Guru')
                    ->collapsible(),
            ])
            ->defaultGroup('guru.user.name')
            ->columns([
                TextColumn::make('mapel.nama')
                    ->label('Mata Pelajaran')
                    ->description(fn(GuruMengajar $record): string => $record->mapel->kode)
                    ->searchable(),

                TextColumn::make('kelas.nama')
                    ->label('Kelas')
                    ->badge(),

                TextColumn::make('jam_per_minggu')
                    ->label('Jam/Minggu')
                    ->alignCenter(),

                TextColumn::make('kkm')
                    ->label('KKM')
                    ->numeric()
                    ->alignCenter()
                    ->color(fn(int $state): string => $state < 75 ? 'danger' : 'success'),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),
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
                    ->default(fn() => TahunAjaran::getActive()->id),

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
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum ada data penugasan guru');
    }
}
