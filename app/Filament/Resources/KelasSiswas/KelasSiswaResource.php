<?php

namespace App\Filament\Resources\KelasSiswas;

use App\Filament\Resources\KelasSiswas\Pages\ListKelasSiswas;
use App\Filament\Resources\KelasSiswas\Schemas\KelasSiswaForm;
use App\Filament\Resources\KelasSiswas\Tables\KelasSiswasTable;
use App\Models\Kelas;
use BackedEnum;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class KelasSiswaResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Kelas::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Akademik';

    protected static ?string $navigationLabel = 'Rombongan Belajar';

    protected static ?string $pluralLabel = 'Rombongan Belajar';

    protected static ?string $recordTitleAttribute = 'nama_kelas_ta';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'rombongan-belajar';

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
        $total = (int) static::getModel()::totalSiswaAktif();

        return $total > 0 ? (string) $total : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function getResourceDescriptor(): string
    {
        return 'rombongan-belajar';
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }

    // public static function canViewAny(): bool
    // {
    //     return Auth::user()->can('view_any_kelas_siswa');
    // }
}
