<?php

namespace App\Filament\Resources\TahunAjarans;

use App\Filament\Resources\TahunAjarans\Pages\ManageTahunAjarans;
use App\Models\TahunAjaran;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class TahunAjaranResource extends Resource
{
    protected static ?string $model = TahunAjaran::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $recordTitleAttribute = 'nama';

    protected static string|UnitEnum|null $navigationGroup = 'Data Utama';

    protected static ?string $navigationLabel = 'Tahun Pelajaran';

    protected static ?string $pluralLabel = 'Tahun Pelajaran';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
                    ->label('Tahun Ajaran')
                    ->required()
                    ->maxLength(9)
                    ->placeholder('Contoh: 2024/2025')
                    ->helperText('Format: Tahun/Tahun'),

                Select::make('semester')
                    ->options([
                        1 => 'Semester 1 (Ganjil)',
                        2 => 'Semester 2 (Genap)',
                    ])
                    ->required()
                    ->default(1),

                DatePicker::make('tanggal_awal')
                    ->required()
                    ->label('Tanggal Mulai'),

                DatePicker::make('tanggal_akhir')
                    ->required()
                    ->label('Tanggal Selesai')
                    ->afterOrEqual('tanggal_mulai'),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->helperText('Hanya satu tahun ajaran yang dapat aktif'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama')
            ->columns([
                TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('semester')
                    ->label('Semester')
                    ->formatStateUsing(fn($state) => $state == 1 ? 'Ganjil' : 'Genap'),

                TextColumn::make('tanggal_awal')
                    ->label('Mulai')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('tanggal_akhir')
                    ->label('Selesai')
                    ->date('d/m/Y')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
            ])
            ->filters([
                SelectFilter::make('semester')
                    ->options([
                        1 => 'Semester 1',
                        2 => 'Semester 2',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->recordActions([
                Action::make('activate')
                    ->label('Aktifkan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn(TahunAjaran $record) => $record->activate())
                    ->requiresConfirmation()
                    ->modalHeading('Aktifkan Tahun Ajaran')
                    ->modalDescription('Tahun ajaran ini akan diaktifkan dan tahun ajaran lainnya akan dinonaktifkan.')
                    ->modalSubmitActionLabel('Ya, Aktifkan')
                    ->hidden(fn(TahunAjaran $record) => $record->is_active),

                EditAction::make(),

                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])->defaultSort('tanggal_awal', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTahunAjarans::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
