<?php

namespace App\Filament\Resources\Jurnals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class JurnalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('guru.user.name')
                    ->label('Guru')
                    ->searchable()
                    ->visible(fn() => Auth::user()->hasAnyRole(['super_admin', 'admin'])),

                TextColumn::make('kelas.nama')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('mapel.nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('materi')
                    ->limit(30)
                    ->tooltip(fn($record) => $record->materi),

                IconColumn::make('status_verifikasi')
                    ->boolean()
                    ->label('Verif'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('guru')
                    ->relationship('guru', 'id')
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->user->name)
                    ->visible(fn() => Auth::user()->hasAnyRole(['super_admin', 'admin'])),

                SelectFilter::make('kelas')
                    ->relationship('kelas', 'nama'),

                TernaryFilter::make('status_verifikasi')
                    ->label('Status Verifikasi'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
