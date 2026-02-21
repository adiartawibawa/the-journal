<?php

namespace App\Filament\Resources\Gurus\RelationManagers;

use App\Models\WaliKelas;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class WaliKelasRelationManager extends RelationManager
{
    protected static string $relationship = 'waliKelas';

    protected static ?string $title = 'Riwayat Wali Kelas';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tahun_ajaran_id')
                    ->relationship('tahunAjaran', 'nama_semester')
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->nama_semester)
                    ->label('Tahun Ajaran')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('kelas_id')
                    ->relationship('kelas', 'nama')
                    ->label('Kelas')
                    ->required()
                    ->searchable()
                    ->preload(),

                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->required()
                    ->rules([
                        fn(Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                            if ($value === true) {
                                // Ambil context data dari form
                                $tahunAjaranId = $get('tahun_ajaran_id');
                                $kelasId = $get('kelas_id');
                                $currentId = $get('id'); // Untuk pengecekan saat mode Edit

                                // Cek apakah sudah ada wali kelas aktif di kelas & tahun ajaran tersebut
                                $exists = WaliKelas::where('tahun_ajaran_id', $tahunAjaranId)
                                    ->where('kelas_id', $kelasId)
                                    ->where('is_active', true)
                                    ->when($currentId, fn($query) => $query->where('id', '!=', $currentId))
                                    ->exists();

                                if ($exists) {
                                    $fail("Gagal mengaktifkan. Masih terdapat Wali Kelas aktif pada Kelas dan Tahun Ajaran yang dipilih. Nonaktifkan terlebih dahulu pejabat lama.");
                                }
                            }
                        },
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.nama')
            ->columns([
                TextColumn::make('tahunAjaran.nama_semester')
                    ->label('Tahun Ajaran')
                    ->sortable(),

                TextColumn::make('kelas.nama')
                    ->label('Kelas')
                    ->sortable()
                    ->searchable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Hanya Aktif'),
            ])
            ->headerActions([
                CreateAction::make(),
                // AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                // DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
