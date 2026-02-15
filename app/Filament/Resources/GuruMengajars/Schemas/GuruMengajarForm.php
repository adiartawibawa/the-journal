<?php

namespace App\Filament\Resources\GuruMengajars\Schemas;

use App\Models\Guru;
use App\Models\TahunAjaran;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GuruMengajarForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Guru Mengajar')
                    ->description('')
                    ->schema([
                        Select::make('guru_id')
                            ->label('Guru')
                            ->options(function () {
                                return Guru::with('user')
                                    ->get()
                                    ->mapWithKeys(function ($guru) {
                                        return [
                                            $guru->id => $guru->user?->name ?? "Guru ID: {$guru->id}"
                                        ];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->required()
                            ->preload(),

                        Select::make('mapel_id')
                            ->label('Mata Pelajaran')
                            ->relationship('mapel', 'nama')
                            ->searchable()
                            ->required()
                            ->preload(),

                        Select::make('tahun_ajaran_id')
                            ->label('Tahun Ajaran')
                            ->relationship('tahunAjaran', 'nama')
                            ->searchable()
                            ->required()
                            ->preload()
                            ->options(function () {
                                return TahunAjaran::where('is_active', true)
                                    ->pluck('nama', 'id')
                                    ->toArray();
                            })
                            ->default(function () {
                                // Set default ke tahun ajaran yang aktif
                                $activeTahunAjaran = TahunAjaran::where('is_active', true)->first();
                                return $activeTahunAjaran?->id;
                            }),

                        Select::make('kelas_id')
                            ->label('Kelas')
                            ->relationship('kelas', 'nama')
                            ->searchable()
                            ->required()
                            ->preload(),

                        TextInput::make('kkm')
                            ->label('KKM')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->required()
                            ->default(75),

                        TextInput::make('jam_per_minggu')
                            ->label('Jam Per Minggu')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(40)
                            ->required()
                            ->default(4),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ])
                    ->columnSpanFull()
            ]);
    }
}
