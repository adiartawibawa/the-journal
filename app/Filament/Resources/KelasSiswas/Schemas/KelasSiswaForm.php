<?php

namespace App\Filament\Resources\KelasSiswas\Schemas;

use App\Models\Kelas;
use App\Models\KelasSiswa;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

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
                                    ->options(TahunAjaran::all()->pluck('nama', 'id'))
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
                                    ->options(function () {
                                        return Kelas::active()
                                            ->orderBy('tingkat')
                                            ->orderBy('nama')
                                            ->get()
                                            ->mapWithKeys(function ($kelas) {
                                                $nama = $kelas->nama . ' (Tingkat ' . $kelas->tingkat . ')';
                                                if ($kelas->jurusan) {
                                                    $nama .= ' - ' . $kelas->jurusan;
                                                }
                                                return [$kelas->id => $nama];
                                            });
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $kelas = Kelas::find($state);
                                        if ($kelas) {
                                            $existingCount = KelasSiswa::where('kelas_id', $state)
                                                ->where('tahun_ajaran_id', $get('tahun_ajaran_id'))
                                                ->where('status', 'aktif')
                                                ->count();

                                            if ($existingCount >= $kelas->kapasitas) {
                                                Notification::make()
                                                    ->warning()
                                                    ->title('Kelas Sudah Penuh')
                                                    ->body("Kelas {$kelas->nama} sudah mencapai kapasitas maksimal ({$kelas->kapasitas} siswa)")
                                                    ->send();
                                            }
                                        }
                                    }),

                                Select::make('siswa_id')
                                    ->label('Siswa')
                                    ->options(function () {
                                        return Siswa::with('user')
                                            ->active()
                                            ->get()
                                            ->mapWithKeys(function ($siswa) {
                                                return [$siswa->id => $siswa->user?->name . ' (' . ($siswa->nisn ?? 'NISN tidak ada') . ')'];
                                            });
                                    })
                                    ->required()
                                    ->searchable(['nisn'])
                                    ->getSearchResultsUsing(function (string $search) {
                                        return Siswa::with('user')
                                            ->whereHas('user', function ($query) use ($search) {
                                                $query->where('name', 'like', "%{$search}%");
                                            })
                                            ->orWhere('nisn', 'like', "%{$search}%")
                                            ->active()
                                            ->limit(10)
                                            ->get()
                                            ->mapWithKeys(function ($siswa) {
                                                return [$siswa->id => $siswa->user?->name . ' (' . ($siswa->nisn ?? 'NISN tidak ada') . ')'];
                                            });
                                    })
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->label('Nama')
                                            ->required(),
                                        TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->required()
                                            ->unique('users', 'email'),
                                        TextInput::make('password')
                                            ->label('Password')
                                            ->password()
                                            ->required()
                                            ->default('password'),
                                        TextInput::make('nisn')
                                            ->label('NISN')
                                            ->unique('siswas', 'nisn'),
                                    ])
                                    ->createOptionUsing(function (array $data) {
                                        $user = User::create([
                                            'name' => $data['name'],
                                            'email' => $data['email'],
                                            'password' => bcrypt($data['password']),
                                        ]);

                                        $user->assignRole('siswa');

                                        return Siswa::create([
                                            'user_id' => $user->id,
                                            'nisn' => $data['nisn'],
                                            'is_active' => true,
                                        ])->id;
                                    }),
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
