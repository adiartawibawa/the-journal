<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(fn($record) => $record->avatar_url)
                    ->size(40)
                    ->extraImgAttributes(['loading' => 'lazy']),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn($record) => $record->email),

                BadgeColumn::make('email_verified_at')
                    ->label('Verifikasi Email')
                    ->formatStateUsing(fn($state) => $state ? 'Verified' : 'Unverified')
                    ->color(fn($state) => $state ? 'success' : 'warning')
                    ->icon(fn($state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle'),

                BadgeColumn::make('roles.name')
                    ->label('Roles')
                    ->colors(['primary'])
                    ->separator(',')
                    ->formatStateUsing(fn($state) => $state ?? 'No Role'),

                // TextColumn::make('profileGuru.nama_lengkap')
                //     ->label('Nama Guru')
                //     ->toggleable(isToggledHiddenByDefault: true)
                //     ->searchable(),

                // TextColumn::make('profileSiswa.nama_lengkap')
                //     ->label('Nama Siswa')
                //     ->toggleable(isToggledHiddenByDefault: true)
                //     ->searchable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diupdate')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label('Dihapus')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->badge()
                    ->color('danger'),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->label('Filter by Role'),

                SelectFilter::make('verification')
                    ->label('Verification Status')
                    ->options([
                        'verified' => 'Verified',
                        'unverified' => 'Unverified',
                    ])
                    ->query(function ($query, array $data) {
                        return match ($data['value'] ?? null) {
                            'verified' => $query->whereNotNull('email_verified_at'),
                            'unverified' => $query->whereNull('email_verified_at'),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->label('Hapus')
                    ->icon('heroicon-m-trash'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
