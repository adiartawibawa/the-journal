<?php

namespace App\Filament\Resources\KelasSiswas;

use App\Filament\Resources\KelasSiswas\Pages\CreateKelasSiswa;
use App\Filament\Resources\KelasSiswas\Pages\EditKelasSiswa;
use App\Filament\Resources\KelasSiswas\Pages\ListKelasSiswas;
use App\Filament\Resources\KelasSiswas\Schemas\KelasSiswaForm;
use App\Filament\Resources\KelasSiswas\Tables\KelasSiswasTable;
use App\Models\KelasSiswa;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class KelasSiswaResource extends Resource
{
    protected static ?string $model = KelasSiswa::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'periode';

    protected static string|UnitEnum|null $navigationGroup = 'Penugasan';

    protected static ?string $navigationLabel = 'Kelas Siswa';

    protected static ?string $pluralLabel = 'Kelas Siswa';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return KelasSiswaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KelasSiswasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKelasSiswas::route('/'),
            'create' => CreateKelasSiswa::route('/create'),
            'edit' => EditKelasSiswa::route('/{record}/edit'),
        ];
    }
}
