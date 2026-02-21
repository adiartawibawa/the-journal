<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\BebanMengajarChart;
use App\Filament\Widgets\JurusanChart;
use App\Filament\Widgets\PenugasanStats;
use App\Filament\Widgets\SiswaStatsOverview;
use App\Models\TahunAjaran;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Widgets\AccountWidget;

class Dashboard extends BaseDashboard
{

    use HasFiltersForm;

    // protected function getDefaultFilters(): array
    // {
    //     return [
    //         'tahun_ajaran_id' => TahunAjaran::getActive()?->id,
    //     ];
    // }

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
                            ->options(fn() => TahunAjaran::get()->pluck('nama_semester', 'id'))
                            ->default(fn() => TahunAjaran::getActive()?->id)
                            ->selectablePlaceholder(false)
                            ->preload(),
                    ])->columnSpanFull(),
            ]);
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 3;
    }

    /**
     * Menentukan daftar widget yang hanya muncul di halaman Dashboard.
     */
    public function getWidgets(): array
    {
        return [
            // SiswaStatsOverview::class,
            // PenugasanStats::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            // JurusanChart::class,
            // BebanMengajarChart::class,
        ];
    }

    public function getFooterWidgetsColumns(): int|array
    {
        return 2;
    }
}
