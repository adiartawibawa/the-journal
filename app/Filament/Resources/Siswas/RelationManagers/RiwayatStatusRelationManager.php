<?php

namespace App\Filament\Resources\Siswas\RelationManagers;

use App\Filament\Resources\Siswas\SiswaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RiwayatStatusRelationManager extends RelationManager
{
    protected static string $relationship = 'riwayatStatus';

    protected static ?string $title = 'Timeline Riwayat Status';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal_perubahan')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('status_lama')
                    ->label('Status Sebelumnya')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('status_baru')
                    ->label('Status Baru')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'aktif' => 'success',
                        'keluar' => 'danger',
                        'lulus' => 'info',
                        'baru' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('alasan')
                    ->label('Keterangan / Alasan')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Waktu Pencatatan')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->defaultSort('created_at', 'desc');
    }
}
