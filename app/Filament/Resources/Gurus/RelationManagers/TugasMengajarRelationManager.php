<?php

namespace App\Filament\Resources\Gurus\RelationManagers;

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
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TugasMengajarRelationManager extends RelationManager
{
    protected static string $relationship = 'tugasMengajar';

    protected static ?string $title = 'Tugas Mengajar';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Select::make('tahun_ajaran_id')
                            ->relationship('tahunAjaran', 'nama')
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->nama_semester)
                            ->label('Tahun Ajaran')
                            ->required()
                            ->preload(),

                        Select::make('kelas_id')
                            ->relationship('kelas', 'nama')
                            ->label('Kelas')
                            ->required()
                            ->preload(),

                        Select::make('mapel_id')
                            ->relationship('mapel', 'nama')
                            ->label('Mata Pelajaran')
                            ->required()
                            ->preload(),

                        TextInput::make('jam_per_minggu')
                            ->label('Jam/Minggu')
                            ->numeric()
                            ->default(2)
                            ->required(),

                        TextInput::make('kkm')
                            ->label('KKM')
                            ->numeric()
                            ->default(75)
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('tahunAjaran.nama_semester')
                    ->label('Tahun Ajaran'),

                TextColumn::make('kelas.nama')
                    ->label('Kelas')
                    ->sortable(),

                TextColumn::make('mapel.nama')
                    ->label('Mata Pelajaran')
                    ->searchable(),

                TextColumn::make('jam_per_minggu')
                    ->label('Jam')
                    ->badge()
                    ->color('info'),

                TextColumn::make('kkm')
                    ->label('KKM'),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),
            ])
            ->filters([
                //
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
