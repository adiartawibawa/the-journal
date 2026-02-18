<?php

namespace App\Filament\Resources\KelasSiswas;

use App\Filament\Resources\KelasSiswas\Pages\ListKelasSiswas;
use App\Filament\Resources\KelasSiswas\Schemas\KelasSiswaForm;
use App\Filament\Resources\KelasSiswas\Tables\KelasSiswasTable;
use App\Models\Kelas;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class KelasSiswaResource extends Resource
{
    protected static ?string $model = Kelas::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Penugasan';

    protected static ?string $navigationLabel = 'Siswa per Kelas';

    protected static ?string $pluralLabel = 'Siswa per Kelas';

    protected static ?string $recordTitleAttribute = 'nama_kelas_ta';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'siswa-per-kelas';

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
            RelationManagers\KelasSiswaRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKelasSiswas::route('/'),
            'view' => Pages\ViewKelasSiswa::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        // Mengambil jumlah total secara global
        $total = static::getModel()::totalSiswaAktif();

        return $total > 0 ? (string) $total : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
