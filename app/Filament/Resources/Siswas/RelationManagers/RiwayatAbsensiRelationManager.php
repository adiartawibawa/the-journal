<?php

namespace App\Filament\Resources\Siswas\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RiwayatAbsensiRelationManager extends RelationManager
{
    protected static string $relationship = 'riwayatAbsensi';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('status')
            ->columns([
                TextColumn::make('jurnal.tanggal')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                TextColumn::make('jurnal.guru.user.name')
                    ->label('Guru'),
                TextColumn::make('jurnal.mapel.nama')
                    ->label('Mata Pelajaran'),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'danger' => 'Alpha',
                        'warning' => 'Sakit',
                        'primary' => 'Izin',
                        'success' => 'Hadir',
                    ]),
                TextColumn::make('keterangan')
                    ->label('Catatan')
                    ->limit(30),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'Hadir' => 'Hadir',
                        'Sakit' => 'Sakit',
                        'Izin' => 'Izin',
                        'Alpha' => 'Alpha',
                    ]),
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
