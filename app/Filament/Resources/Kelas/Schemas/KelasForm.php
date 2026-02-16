<?php

namespace App\Filament\Resources\Kelas\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class KelasForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode')
                    ->label('Kode Kelas')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true)
                    ->placeholder('Contoh: 2025/2026-X-2'),

                TextInput::make('nama')
                    ->label('Nama Kelas')
                    ->required()
                    ->maxLength(50)
                    ->placeholder('Contoh: X - 2'),

                Select::make('tingkat')
                    ->options([
                        '10' => 'Kelas 10',
                        '11' => 'Kelas 11',
                        '12' => 'Kelas 12',
                    ])
                    ->required()
                    ->searchable(),

                TextInput::make('jurusan')
                    ->label('Jurusan')
                    ->maxLength(50)
                    ->placeholder('Contoh: IPA, IPS, Bahasa'),

                TextInput::make('kapasitas')
                    ->label('Kapasitas Siswa')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(40)
                    ->default(40),

                Textarea::make('deskripsi')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->columnSpanFull(),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}
