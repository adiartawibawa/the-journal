<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Hapus')
                ->icon('heroicon-o-trash')
                ->modalHeading('Hapus User')
                ->modalDescription('Apakah Anda yakin ingin menghapus user ini?')
                ->modalSubmitActionLabel('Ya, Hapus')
                ->modalCancelActionLabel('Batal'),

            ForceDeleteAction::make()
                ->label('Hapus Permanen')
                ->icon('heroicon-o-x-circle')
                ->modalHeading('Hapus Permanen User')
                ->modalDescription('User akan dihapus secara permanen dan tidak dapat dikembalikan!')
                ->modalSubmitActionLabel('Ya, Hapus Permanen')
                ->modalCancelActionLabel('Batal'),

            RestoreAction::make()
                ->label('Restore')
                ->icon('heroicon-o-arrow-path')
                ->modalHeading('Restore User')
                ->modalDescription('Kembalikan user yang telah dihapus?')
                ->modalSubmitActionLabel('Ya, Restore')
                ->modalCancelActionLabel('Batal'),
        ];
    }
}
