<?php

namespace App\Filament\Resources\Jurnals\Tables;

use App\Filament\Resources\Jurnals\Schemas\JurnalInfolist;
use App\Models\Jurnal;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
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
                Filter::make('tanggal')
                    ->schema([
                        DatePicker::make('dari'),
                        DatePicker::make('sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['dari'], fn($q) => $q->whereDate('tanggal', '>=', $data['dari']))
                            ->when($data['sampai'], fn($q) => $q->whereDate('tanggal', '<=', $data['sampai']));
                    }),

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
                Action::make('print')
                    ->label('Cetak PDF')
                    ->icon('heroicon-m-printer')
                    ->color('success')
                    ->url(fn(Jurnal $record): string => route('jurnal.print', $record))
                    ->openUrlInNewTab(),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('print_selected')
                        ->label('Cetak Laporan Terpilih')
                        ->icon('heroicon-m-printer')
                        ->color('success')
                        ->action(function (Collection $records) {
                            session()->put('print_ids', $records->pluck('id')->toArray());
                            return redirect()->route('jurnal.print.bulk');
                        })->openUrlInNewTab(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
