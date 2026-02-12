<?php

namespace App\Filament\Resources\Mapels;

use App\Filament\Resources\Mapels\Pages\ManageMapels;
use App\Models\Mapel;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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

class MapelResource extends Resource
{
    protected static ?string $model = Mapel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentDuplicate;

    protected static ?string $recordTitleAttribute = 'nama';

    protected static string|UnitEnum|null $navigationGroup = 'Data Utama';

    protected static ?string $navigationLabel = 'Mata Pelajaran';

    protected static ?string $pluralLabel = 'Mata Pelajaran';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode')
                    ->label('Kode Mapel')
                    ->required()
                    ->maxLength(10)
                    ->unique(ignoreRecord: true)
                    ->placeholder('Contoh: MAT-10'),

                TextInput::make('nama')
                    ->label('Nama Mata Pelajaran')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('Contoh: Matematika'),

                Select::make('kelompok')
                    ->options([
                        'A' => 'Kelompok A (Wajib)',
                        'B' => 'Kelompok B (Wajib)',
                        'C' => 'Kelompok C (Peminatan)',
                        'L' => 'Muatan Lokal',
                        'E' => 'Ekstrakurikuler',
                    ])
                    ->required()
                    ->searchable(),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama')
            ->columns([
                TextColumn::make('kode')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama')
                    ->label('Nama Mata Pelajaran')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kelompok')
                    ->label('Kelompok')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'A' => 'primary',
                        'B' => 'success',
                        'C' => 'warning',
                        'L' => 'info',
                        'E' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match ($state) {
                        'A' => 'Wajib A',
                        'B' => 'Wajib B',
                        'C' => 'Peminatan',
                        'L' => 'Muatan Lokal',
                        'E' => 'Ekstrakurikuler',
                        default => $state,
                    }),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
            ])
            ->filters([
                SelectFilter::make('kelompok')
                    ->options([
                        'A' => 'Kelompok A',
                        'B' => 'Kelompok B',
                        'C' => 'Kelompok C',
                        'L' => 'Muatan Lokal',
                        'E' => 'Ekstrakurikuler',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('kelompok')
            ->reorderable('nama');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMapels::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::active()->count();
    }
}
