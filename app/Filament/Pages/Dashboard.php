<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AbsensiSiswaOverview;
use App\Filament\Widgets\BebanMengajarChart;
use App\Filament\Widgets\DaftarAbsensiSiswa;
use App\Filament\Widgets\DaftarKelasTanpaWali;
use App\Filament\Widgets\JurnalPeriodicStats;
use App\Filament\Widgets\JurusanChart;
use App\Filament\Widgets\KelasTanpaWaliStats;
use App\Filament\Widgets\PenugasanStats;
use App\Filament\Widgets\SiswaStatsOverview;
use App\Models\TahunAjaran;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    /**
     * Mendefinisikan struktur form filter di atas widget.
     */
    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filter Data Akademik')
                    ->description('Pilih periode untuk menyesuaikan statistik dashboard.')
                    ->schema([
                        Select::make('tahun_ajaran_id')
                            ->label('Tahun Ajaran')
                            ->options(fn() => TahunAjaran::query()
                                ->orderBy('tanggal_awal', 'desc')
                                ->get()
                                ->mapWithKeys(fn($ta) => [
                                    $ta->id => $ta->nama . ' - ' . $ta->semester->getLabel() .
                                        ($ta->is_active ? ' (Aktif)' : '')
                                ]))
                            ->default(fn() => TahunAjaran::getActive()?->id)
                            ->selectablePlaceholder(false)
                            ->preload()
                            ->live(),
                    ])->columnSpanFull(),
            ]);
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 3;
    }

    public function getWidgets(): array
    {
        return [
            SiswaStatsOverview::class,
            PenugasanStats::class,
            KelasTanpaWaliStats::class,
            JurusanChart::class,
            // AbsensiSiswaOverview::class,
            // DaftarAbsensiSiswa::class,
            BebanMengajarChart::class,
            JurnalPeriodicStats::class,
            DaftarKelasTanpaWali::class,
        ];
    }
}
