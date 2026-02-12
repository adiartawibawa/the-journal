<?php

namespace App\Filament\Resources\Gurus\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GurusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.name')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama Guru')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nuptk')
                    ->label('NUPTK')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status_kepegawaian')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'PNS' => 'success',
                        'PPPK' => 'info',
                        'Guru Honor' => 'warning',
                        'Staff Honor' => 'primary',
                        'Kontrak' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('bidang_studi')
                    ->label('Bidang Studi')
                    ->searchable()
                    ->sortable()
                    ->limit(20),

                TextColumn::make('golongan')
                    ->badge(),

                TextColumn::make('pendidikan_terakhir')
                    ->searchable(),

            ])
            ->filters([
                SelectFilter::make('status_kepegawaian')
                    ->options([
                        'PNS' => 'PNS',
                        'PPPK' => 'PPPK',
                        'Guru Honor' => 'Guru Honor',
                        'Staff Honor' => 'Staff Honor',
                        'Kontrak' => 'Kontrak',
                    ]),

                SelectFilter::make('pendidikan_terakhir')
                    ->options([
                        'SMA/SMK' => 'SMA/SMK',
                        'D3' => 'Diploma 3 (D3)',
                        'S1' => 'Sarjana (S1)',
                        'S2' => 'Magister (S2)',
                        'S3' => 'Doktor (S3)',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
