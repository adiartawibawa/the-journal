<?php

namespace App\Filament\Resources\Kelas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class KelasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama')
            ->columns([
                TextColumn::make('kode')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama')
                    ->label('Nama Kelas')
                    ->searchable(['nama_kelas', 'jurusan'])
                    ->sortable(),

                TextColumn::make('tingkat')
                    ->label('Tingkat')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'X' => 'primary',
                        'XI' => 'success',
                        'XII' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('kapasitas')
                    ->label('Kapasitas')
                    ->numeric()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

            ])
            ->filters([
                SelectFilter::make('tingkat')
                    ->options([
                        'X' => 'Kelas 10',
                        'XI' => 'Kelas 11',
                        'XII' => 'Kelas 12',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('tingkat')
            ->reorderable('nama');
    }
}
