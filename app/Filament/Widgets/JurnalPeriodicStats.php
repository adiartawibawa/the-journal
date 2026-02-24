<?php

namespace App\Filament\Widgets;

use App\Models\Jurnal;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class JurnalPeriodicStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Jurnal Hari Ini', Jurnal::whereDate('tanggal', Carbon::today())->count())
                ->description('Total jam mengajar hari ini')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('info'),

            Stat::make('Jurnal Minggu Ini', Jurnal::whereBetween('tanggal', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count())
                ->description('Akumulasi dalam satu minggu')
                ->color('warning'),

            Stat::make('Jurnal Bulan Ini', Jurnal::whereMonth('tanggal', Carbon::now()->month)->count())
                ->description('Total performa bulanan')
                ->color('success'),
        ];
    }
}
