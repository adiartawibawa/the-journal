<?php

namespace App\Filament\Resources\Jurnals\Pages;

use App\Filament\Resources\Jurnals\JurnalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListJurnals extends ListRecords
{
    protected static string $resource = JurnalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
