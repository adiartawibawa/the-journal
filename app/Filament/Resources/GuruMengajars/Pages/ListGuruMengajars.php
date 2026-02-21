<?php

namespace App\Filament\Resources\GuruMengajars\Pages;

use App\Filament\Resources\GuruMengajars\GuruMengajarResource;
use App\Filament\Widgets\BebanMengajarChart;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGuruMengajars extends ListRecords
{
    protected static string $resource = GuruMengajarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BebanMengajarChart::class,
        ];
    }
}
