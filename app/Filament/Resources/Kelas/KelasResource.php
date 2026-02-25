<?php

namespace App\Filament\Resources\Kelas;

use App\Filament\Resources\Kelas\Pages\CreateKelas;
use App\Filament\Resources\Kelas\Pages\EditKelas;
use App\Filament\Resources\Kelas\Pages\ListKelas;
use App\Filament\Resources\Kelas\Schemas\KelasForm;
use App\Filament\Resources\Kelas\Tables\KelasTable;
use App\Models\Kelas;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class KelasResource extends Resource
{
    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        if (!$user || $user->hasRole('teacher') || $user->hasRole('student')) {
            return false;
        }

        return true;
    }

    protected static ?string $model = Kelas::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nama';

    protected static string|UnitEnum|null $navigationGroup = 'Data Utama';

    protected static ?string $navigationLabel = 'Kelas';

    protected static ?string $pluralLabel = 'Kelas';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return KelasForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KelasTable::configure($table);
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
            'index' => ListKelas::route('/'),
            'create' => CreateKelas::route('/create'),
            'edit' => EditKelas::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::active()->count();
    }
}
