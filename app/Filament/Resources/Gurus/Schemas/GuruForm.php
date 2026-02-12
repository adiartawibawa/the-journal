<?php

namespace App\Filament\Resources\Gurus\Schemas;

use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
                        TextInput::make('user.name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('user.email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(User::class, 'email', ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create')
                            ->maxLength(255)
                            ->helperText(
                                fn(string $context): string =>
                                $context === 'create' ? 'Password untuk login' : 'Kosongkan jika tidak ingin mengubah password'
                            ),
                    ])->columnSpanFull(),

                Section::make('Data Profesional')
                    ->description('Informasi Profesional guru')
                    ->collapsible()
                    ->schema([
                        TextInput::make('nuptk')
                            ->label('NUPTK')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->length(16)
                            ->numeric(),

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
                            ->options(['I' => 'I', 'II' => 'II', 'III' => 'III', 'IV' => 'IV'])
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
