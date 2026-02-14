<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun')
                    ->description('Data dasar untuk login dan identifikasi user')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Lengkap')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Masukkan nama lengkap')
                                    ->autofocus(),

                                TextInput::make('email')
                                    ->label('Alamat Email')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->placeholder('nama@email.com')
                                    ->suffixIcon('heroicon-m-envelope'),

                                TextInput::make('password')
                                    ->label('Password')
                                    ->password()
                                    ->required(fn(string $operation): bool => $operation === 'create')
                                    ->dehydrated(fn($state) => filled($state))
                                    ->rule(Password::default())
                                    ->same('password_confirmation')
                                    ->maxLength(255)
                                    ->placeholder('Minimal 8 karakter')
                                    ->revealable(),

                                TextInput::make('password_confirmation')
                                    ->label('Konfirmasi Password')
                                    ->password()
                                    ->required(fn(string $operation): bool => $operation === 'create')
                                    ->dehydrated(false)
                                    ->placeholder('Ketik ulang password')
                                    ->revealable(),
                            ]),
                    ]),

                Section::make('Foto Profile')
                    ->description('Upload foto profile user (maksimal 2MB)')
                    ->icon('heroicon-o-camera')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('avatar')
                            ->label('Foto Profile')
                            ->collection('avatar')
                            ->image()
                            ->maxSize(2048) // 2MB
                            ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp'])
                            ->disk('public')
                            ->directory('avatars')
                            ->visibility('public')
                            ->preserveFilenames(false)
                            ->downloadable()
                            ->openable()
                            ->previewable(true)
                            ->columnSpanFull()
                            ->helperText('Format: JPG, PNG, WebP. Maksimal 2MB. Ukuran terbaik 300x300px.'),
                    ])
                    ->collapsible(),

                Section::make('Role & Permissions')
                    ->description('Atur hak akses user dalam sistem')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Select::make('roles')
                            ->label('Roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->required()
                            ->placeholder('Pilih role')
                            ->helperText('User dapat memiliki lebih dari satu role'),
                    ]),

                Section::make('Profile')
                    ->description('Informasi tambahan user')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Toggle::make('send_welcome_email')
                            ->label('Kirim Email Selamat Datang')
                            ->default(true)
                            ->visible(fn(string $operation): bool => $operation === 'create')
                            ->helperText('Email akan dikirim setelah user dibuat'),
                    ]),
            ]);
    }
}
