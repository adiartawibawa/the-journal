<?php

namespace App\Filament\Pages;

use App\Settings\GeneralSettings;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageGeneralSetApp extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string $settings = GeneralSettings::class;

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Identitas Sekolah';

    protected static ?string $title = 'Pengaturan Global Sekolah';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Settings')
                    ->tabs([
                        // TAB 1: PROFIL & LEMBAGA
                        Tab::make('Profil & Lembaga')
                            ->icon('heroicon-o-home-modern')
                            ->schema([
                                Section::make('Identitas Dasar')
                                    ->description('Informasi utama mengenai entitas sekolah.')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextInput::make('nama_sekolah')->required()->maxLength(255),
                                            TextInput::make('nama_singkat')->label('Nama Aplikasi')->required()->maxLength(50),
                                            TextInput::make('npsn')->label('NPSN')->required(),
                                            TextInput::make('nss')->label('NSS (Jika ada)'),
                                            Select::make('status_sekolah')
                                                ->options(['Negeri' => 'Negeri', 'Swasta' => 'Swasta'])
                                                ->required()
                                                ->native(false)
                                                ->live(),
                                        ]),
                                    ]),
                                Section::make('Lembaga Naungan')
                                    ->description('Informasi Lembaga Naungan atau SK pendirian.')
                                    ->schema([
                                        TextInput::make('nama_lembaga_naungan')
                                            ->label('Nama Lembaga Naungan')
                                            ->visible(fn(Get $get) => $get('status_sekolah') === 'Swasta'),
                                        Grid::make(2)->schema([
                                            TextInput::make('sk_pendirian_no')->label('Nomor SK Pendirian'),
                                            DatePicker::make('sk_pendirian_tgl')->label('Tanggal SK Pendirian')->native(false),
                                        ]),
                                    ]),
                            ]),

                        // TAB 2: KONTAK & LOKASI
                        Tab::make('Kontak & Lokasi')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Section::make('Alamat Lengkap')
                                    ->schema([
                                        Textarea::make('alamat')->required()->rows(2),
                                        Grid::make(3)->schema([
                                            TextInput::make('rt_rw')->label('RT / RW'),
                                            TextInput::make('kelurahan')->required(),
                                            TextInput::make('kecamatan')->required(),
                                            TextInput::make('kab_kota')->label('Kabupaten/Kota')->required(),
                                            TextInput::make('provinsi')->required(),
                                            TextInput::make('kode_pos')->numeric(),
                                        ]),
                                    ]),
                                Section::make('Kontak Resmi')
                                    ->schema([
                                        Grid::make(3)->schema([
                                            TextInput::make('telepon')->tel(),
                                            TextInput::make('email')->email(),
                                            TextInput::make('website')->url(),
                                        ]),
                                    ]),
                            ]),

                        // TAB 3: AKREDITASI & PIMPINAN
                        Tab::make('Akreditasi & Pimpinan')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([
                                Section::make('Data Akreditasi')
                                    ->columns(2)
                                    ->schema([
                                        Select::make('akreditasi_status')
                                            ->options(['A' => 'Terakreditasi A', 'B' => 'Terakreditasi B', 'C' => 'Terakreditasi C', 'Belum' => 'Belum Terakreditasi'])
                                            ->native(false),
                                        TextInput::make('akreditasi_sk')->label('Nomor SK Akreditasi'),
                                        TextInput::make('akreditasi_tahun')->label('Tahun Akreditasi')->numeric(),
                                        DatePicker::make('akreditasi_tgl_kadaluarsa')->label('Tanggal Kadaluarsa')->native(false),
                                    ]),
                                Section::make('Pejabat Sekolah')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('nama_kepala_sekolah')->required(),
                                        TextInput::make('nip_kepala_sekolah')->label('NIP Kepala Sekolah'),
                                    ]),
                            ]),

                        // TAB 4: VISUAL & LAPORAN
                        Tab::make('Visual & Laporan')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Section::make('Branding')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            FileUpload::make('logo_sekolah')
                                                ->image()
                                                ->directory('settings')
                                                ->imageEditor(),
                                            FileUpload::make('ttd_digital')
                                                ->label('Tanda Tangan Kepala Sekolah (PNG)')
                                                ->image()
                                                ->directory('settings'),
                                        ]),
                                        TextInput::make('motto')->placeholder('Semboyan sekolah...'),
                                        Select::make('kop_surat_template')
                                            ->options([
                                                'default' => 'Template Standar',
                                                'modern' => 'Template Modern',
                                                'minimalis' => 'Tanpa Garis Pemisah',
                                            ])
                                            ->native(false),
                                    ]),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
