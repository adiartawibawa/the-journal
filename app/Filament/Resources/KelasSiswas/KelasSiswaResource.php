<?php

namespace App\Filament\Resources\KelasSiswas;

use App\Filament\Resources\KelasSiswas\Pages\CreateKelasSiswa;
use App\Filament\Resources\KelasSiswas\Pages\EditKelasSiswa;
use App\Filament\Resources\KelasSiswas\Pages\ListKelasSiswas;
use App\Filament\Resources\KelasSiswas\Schemas\KelasSiswaForm;
use App\Filament\Resources\KelasSiswas\Tables\KelasSiswasTable;
use App\Models\Kelas;
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

    protected static ?string $navigationLabel = 'Siswa Per Kelas';

    protected static ?string $modelLabel = 'Siswa Kelas';

    protected static ?string $pluralLabel = 'Siswa Per Kelas';

    protected static ?string $slug = 'siswa-per-kelas';

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

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::query()
            ->whereHas('tahunAjaran', fn($query) => $query->where('is_active', true))
            ->aktif()
            ->count();
    }
}
