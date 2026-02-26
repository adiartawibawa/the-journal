<?php

namespace App\Filament\Resources\Jurnals;

use App\Filament\Resources\Jurnals\Pages\CreateJurnal;
use App\Filament\Resources\Jurnals\Pages\EditJurnal;
use App\Filament\Resources\Jurnals\Pages\ListJurnals;
use App\Filament\Resources\Jurnals\Pages\ViewJurnal;
use App\Filament\Resources\Jurnals\Schemas\JurnalForm;
use App\Filament\Resources\Jurnals\Schemas\JurnalInfolist;
use App\Filament\Resources\Jurnals\Tables\JurnalsTable;
use App\Models\Jurnal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class JurnalResource extends Resource
{
    protected static ?string $model = Jurnal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Kegiatan Belajar Mengajar';

    protected static ?string $label = 'Jurnal Mengajar';

    protected static ?string $modelLabel = 'Jurnal Mengajar';

    protected static ?string $pluralLabel = 'Jurnal Mengajar';

    protected static ?string $recordTitleAttribute = 'materi';

    public static function getNavigationSort(): ?int
    {
        $authUser = Auth::user();

        if ($authUser && $authUser->hasRole('teacher')) {
            return 1;
        }

        return 2;
    }

    public static function form(Schema $schema): Schema
    {
        return JurnalForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return JurnalInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JurnalsTable::configure($table);
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
            'index' => ListJurnals::route('/'),
            'create' => CreateJurnal::route('/create'),
            'edit' => EditJurnal::route('/{record}/edit'),
            'view' => ViewJurnal::route('/{record}'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::getCountForNavBadge();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
}
