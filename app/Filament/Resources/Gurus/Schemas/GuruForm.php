<?php

namespace App\Filament\Resources\Gurus\Schemas;

use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class GuruForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Akun Guru')
                    ->description('Informasi akun untuk login')
                    ->collapsible()
                    ->schema([
                        Group::make()
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Lengkap')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->unique(
                                        User::class,
                                        'email',
                                        ignoreRecord: true
                                    )
                                    ->maxLength(255),

                                TextInput::make('password')
                                    ->label('Password')
                                    ->password()
                                    ->dehydrateStateUsing(
                                        fn($state) =>
                                        filled($state) ? Hash::make($state) : null
                                    )
                                    ->dehydrated(fn($state) => filled($state))
                                    ->required(fn($operation) => $operation === 'create')
                                    ->maxLength(255)
                                    ->helperText(
                                        fn($operation) =>
                                        $operation === 'create'
                                            ? 'Password untuk login'
                                            : 'Kosongkan jika tidak ingin mengubah password'
                                    ),
                            ])->hidden(fn(string $operation): bool => $operation === 'edit'),

                        Group::make()
                            ->relationship('user')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Lengkap')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->unique(
                                        User::class,
                                        'email',
                                        ignoreRecord: true
                                    )
                                    ->maxLength(255),

                                TextInput::make('password')
                                    ->label('Password')
                                    ->password()
                                    ->dehydrateStateUsing(
                                        fn($state) =>
                                        filled($state) ? Hash::make($state) : null
                                    )
                                    ->dehydrated(fn($state) => filled($state))
                                    ->required(fn($operation) => $operation === 'create')
                                    ->maxLength(255)
                                    ->helperText(
                                        fn($operation) =>
                                        $operation === 'create'
                                            ? 'Password untuk login'
                                            : 'Kosongkan jika tidak ingin mengubah password'
                                    ),
                            ])
                            ->hidden(fn(string $operation): bool => $operation === 'create')
                    ])
                    ->columnSpanFull(),

                Section::make('Data Profesional')
                    ->description('Informasi Profesional guru')
                    ->collapsible()
                    ->schema([
                        TextInput::make('nuptk')
                            ->label('NUPTK')
                            // ->required()
                            ->unique(ignoreRecord: true)
                            ->length(16),

                        Select::make('status_kepegawaian')
                            ->options([
                                'PNS' => 'PNS',
                                'PPPK' => 'PPPK',
                                'Guru Honor' => 'Guru Honor',
                                'Staff Honor' => 'Staff Honor',
                                'Kontrak' => 'Kontrak',
                            ])
                            ->required()
                            ->searchable(),

                        Select::make('golongan')
                            ->options(collect([
                                'I' => ['a', 'b', 'c', 'd'],
                                'II' => ['a', 'b', 'c', 'd'],
                                'III' => ['a', 'b', 'c', 'd'],
                                'IV' => ['a', 'b', 'c', 'd', 'e'],
                            ])->flatMap(
                                fn($subs, $kelas) =>
                                collect($subs)->mapWithKeys(fn($s) => ["$kelas/$s" => "$kelas/$s"])
                            )->toArray())
                            ->default(null),

                        DatePicker::make('tanggal_masuk')
                            ->label('Tanggal Masuk')
                            ->required()
                            ->maxDate(now()),

                        TextInput::make('bidang_studi')
                            ->label('Bidang Studi')
                            ->maxLength(100)
                            ->placeholder('Contoh: Matematika, Bahasa Inggris'),

                        Select::make('pendidikan_terakhir')
                            ->options([
                                'SMA/SMK' => 'SMA/SMK',
                                'D3' => 'Diploma 3 (D3)',
                                'S1' => 'Sarjana (S1)',
                                'S2' => 'Magister (S2)',
                                'S3' => 'Doktor (S3)',
                            ])
                            ->searchable(),

                    ])
                    ->columnSpanFull(),

            ]);
    }
}
