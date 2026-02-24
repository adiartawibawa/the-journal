<?php

namespace App\Filament\Resources\Jurnals\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JurnalInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Utama')
                    ->icon('heroicon-m-information-circle')
                    ->schema([
                        TextEntry::make('tanggal')
                            ->label('Tanggal KBM')
                            ->date('d F Y'),
                        TextEntry::make('kelas.nama')
                            ->label('Kelas')
                            ->badge()
                            ->color('info'),
                        TextEntry::make('mapel.nama')
                            ->label('Mata Pelajaran'),
                        TextEntry::make('guru.user.name')
                            ->label('Guru Pengajar')
                            ->icon('heroicon-m-user'),
                    ])->columns(2),

                Section::make('Detail Materi & Kegiatan')
                    ->icon('heroicon-m-book-open')
                    ->schema([
                        TextEntry::make('materi')
                            ->markdown()
                            ->columnSpanFull(),
                        TextEntry::make('kegiatan')
                            ->markdown()
                            ->columnSpanFull(),
                    ]),

                Section::make('Daftar Ketidakhadiran Siswa')
                    ->icon('heroicon-m-users')
                    ->description('Hanya menampilkan ringkasan siswa yang tidak hadir.')
                    ->schema([
                        KeyValueEntry::make('absensi') // Mengambil dari cast array di model
                            ->label(false)
                            ->keyLabel('Nama Siswa')
                            ->valueLabel('Status & Keterangan'),
                    ]),

                Section::make('Dokumentasi Kegiatan')
                    ->icon('heroicon-m-camera')
                    ->description('Bukti foto pelaksanaan kegiatan belajar mengajar.')
                    ->schema([
                        SpatieMediaLibraryImageEntry::make('foto_kegiatan') // Menggunakan collection name dari Spatie
                            ->label(false)
                            ->collection('foto_kegiatan') // Nama collection di model Jurnal
                            ->disk('public') // Pastikan disk sesuai konfigurasi Anda
                            ->columnSpanFull()
                            ->defaultImageUrl(url('/images/no-image.png')), // Fallback jika tidak ada foto
                    ]),
            ])->columns(1);
    }
}
