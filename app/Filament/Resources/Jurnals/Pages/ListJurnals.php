<?php

namespace App\Filament\Resources\Jurnals\Pages;

use App\Filament\Resources\Jurnals\JurnalResource;
use App\Filament\Widgets\JurnalPeriodicStats;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;

class ListJurnals extends ListRecords
{
    protected static string $resource = JurnalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('printRekap')
                ->label('Cetak Rekapitulasi')
                ->icon('heroicon-m-document-text')
                ->schema([
                    Select::make('periode')
                        ->options([
                            'harian' => 'Hari Ini',
                            'mingguan' => 'Minggu Ini',
                            'bulanan' => 'Bulan Ini',
                            'custom' => 'Rentang Tanggal Custom',
                        ])
                        ->reactive()
                        ->required(),
                    DatePicker::make('dari')
                        ->visible(fn($get) => $get('periode') === 'custom'),
                    DatePicker::make('sampai')
                        ->visible(fn($get) => $get('periode') === 'custom'),
                ])
                ->action(function (array $data) {
                    // Logika Filter Data berdasarkan $data['periode']
                    // Lalu kirim ke Service PDF
                }),

            CreateAction::make()
                ->label('Jurnal Mengajar')
                ->icon(Heroicon::OutlinedPlusCircle),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua'),
            'harian' => Tab::make('Hari Ini')
                ->modifyQueryUsing(fn($query) => $query->whereDate('tanggal', Carbon::today())),
            'mingguan' => Tab::make('Minggu Ini')
                ->modifyQueryUsing(fn($query) => $query->whereBetween('tanggal', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])),
            'bulanan' => Tab::make('Bulan Ini')
                ->modifyQueryUsing(fn($query) => $query->whereMonth('tanggal', Carbon::now()->month)),
        ];
    }
}
