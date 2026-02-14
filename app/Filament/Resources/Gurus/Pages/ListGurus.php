<?php

namespace App\Filament\Resources\Gurus\Pages;

use App\Filament\Imports\GuruImporter;
use App\Filament\Resources\Gurus\GuruResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Symfony\Component\Console\Color;

class ListGurus extends ListRecords
{
    protected static string $resource = GuruResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Guru')
                ->icon(Heroicon::OutlinedUserPlus),

            // ImportAction::make()
            //     ->label('Impor Guru')
            //     ->color('danger')
            //     ->icon(Heroicon::OutlinedArrowDownTray)
            //     ->importer(GuruImporter::class)
            //     ->chunkSize(50)
            //     ->options([
            //         'updateExisting' => true,
            //     ])
        ];
    }
}
