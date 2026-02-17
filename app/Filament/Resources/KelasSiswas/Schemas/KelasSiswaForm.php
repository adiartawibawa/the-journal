<?php

namespace App\Filament\Resources\KelasSiswas\Schemas;

use App\Models\Kelas;
use App\Models\KelasSiswa;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class KelasSiswaForm
{
    public static function configure(Schema $schema): Schema
    {
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();

        return $schema
            ->components([
                Section::make('Informasi Penempatan')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('tahun_ajaran_id')
                                    ->label('Tahun Ajaran')
                                    ->options(function () {
                                        return TahunAjaran::all()->mapWithKeys(function ($tahunAjaran) {
                                            $semesterLabel = $tahunAjaran->semester == '1' ? 'Ganjil' : 'Genap';
                                            return [$tahunAjaran->id => $tahunAjaran->nama . ' - Semester ' . $semesterLabel];
                                        });
                                    })
                                    ->default($tahunAjaranAktif?->id)
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $tahunAjaran = TahunAjaran::find($state);
                                        if ($tahunAjaran) {
                                            $set('tanggal_mulai', $tahunAjaran->tanggal_awal);
                                            $set('tanggal_selesai', $tahunAjaran->tanggal_akhir);
                                        }
                                    }),

                                Select::make('kelas_id')
                                    ->label('Kelas')
                                    ->relationship('kelas', 'nama')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $kelas = Kelas::find($state);
                                            if ($kelas) {
                                                $set('kapasitas', $kelas->kapasitas);
                                                $set('current_count', $kelas->getJumlahSiswaAktifAttribute(request('tahun_ajaran_id')));
                                            }
                                        }
                                    }),

                                Placeholder::make('kapasitas_info')
                                    ->label('Info Kapasitas')
                                    ->content(function ($get) {
                                        $kapasitas = $get('kapasitas');
                                        $current = $get('current_count');

                                        if ($kapasitas && $current !== null) {
                                            return "{$current} / {$kapasitas} siswa terisi";
                                        }
                                        return 'Pilih kelas untuk melihat kapasitas';
                                    })
                                    ->visible(fn($get) => $get('kelas_id') !== null),

                                Select::make('siswa_id')
                                    ->label('Siswa')
                                    ->options(function () {
                                        return Siswa::with('user')
                                            ->get()
                                            ->mapWithKeys(function ($siswa) {
                                                return [$siswa->id => $siswa->user?->name ?? 'Unknown - ' . $siswa->id];
                                            })
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->getSearchResultsUsing(function (string $search) {
                                        return Siswa::whereHas('user', function ($query) use ($search) {
                                            $query->where('name', 'like', "%{$search}%");
                                        })
                                            ->orWhere('nisn', 'like', "%{$search}%")
                                            ->limit(50)
                                            ->get()
                                            ->mapWithKeys(function ($siswa) {
                                                return [$siswa->id => $siswa->user?->name . ' (' . ($siswa->nisn ?? 'No NISN') . ')'];
                                            })
                                            ->toArray();
                                    })
                                    ->getOptionLabelUsing(function ($value) {
                                        $siswa = Siswa::with('user')->find($value);
                                        return $siswa ? $siswa->user?->name . ' (' . ($siswa->nisn ?? 'No NISN') . ')' : 'Unknown';
                                    })
                                    ->required()
                                    ->unique(ignoreRecord: true),

                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'aktif' => 'Aktif',
                                        'pindah' => 'Pindah',
                                        'lulus' => 'Lulus',
                                        'dropout' => 'Dropout',
                                        'tinggal_kelas' => 'Tinggal Kelas',
                                    ])
                                    ->default('aktif')
                                    ->required(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                DatePicker::make('tanggal_mulai')
                                    ->label('Tanggal Mulai')
                                    ->required()
                                    ->default(fn() => $tahunAjaranAktif?->tanggal_awal),

                                DatePicker::make('tanggal_selesai')
                                    ->label('Tanggal Selesai')
                                    ->required()
                                    ->default(fn() => $tahunAjaranAktif?->tanggal_akhir)
                                    ->after('tanggal_mulai'),
                            ]),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'aktif' => 'Aktif',
                                'pindah' => 'Pindah',
                                'lulus' => 'Lulus',
                                'dropout' => 'Drop Out',
                            ])
                            ->default('aktif')
                            ->required()
                            ->live(),

                        Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
