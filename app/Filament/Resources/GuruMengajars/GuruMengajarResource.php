<?php

namespace App\Filament\Resources\GuruMengajars;

use App\Filament\Resources\GuruMengajars\Pages\CreateGuruMengajar;
use App\Filament\Resources\GuruMengajars\Pages\EditGuruMengajar;
use App\Filament\Resources\GuruMengajars\Pages\ListGuruMengajars;
use App\Filament\Resources\GuruMengajars\Schemas\GuruMengajarForm;
use App\Filament\Resources\GuruMengajars\Tables\GuruMengajarsTable;
use App\Models\GuruMengajar;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class GuruMengajarResource extends Resource
{
    protected static ?string $model = GuruMengajar::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static ?string $recordTitleAttribute = 'guru_mengajar';

    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Akademik';

    protected static ?string $pluralLabel = 'Penugasan Guru';

    protected static ?string $navigationLabel = 'Penugasan Guru';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return GuruMengajarForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GuruMengajarsTable::configure($table);
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
            'index' => ListGuruMengajars::route('/'),
            'create' => CreateGuruMengajar::route('/create'),
            'edit' => EditGuruMengajar::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        /**
         * Mengambil jumlah penugasan guru yang aktif pada Tahun Ajaran yang sedang aktif.
         */
        return static::getModel()::where('is_active', true)
            ->whereHas('tahunAjaran', function ($query) {
                $query->where('is_active', true);
            })
            ->count();
    }

    /**
     * Memberikan warna pada badge.
     * warna 'info' atau 'success' digunakan untuk menunjukkan data aktif.
     */
    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }
}
