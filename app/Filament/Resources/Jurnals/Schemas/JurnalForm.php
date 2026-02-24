<?php

namespace App\Filament\Resources\Jurnals\Schemas;

use App\Models\GuruMengajar;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Settings\GeneralSettings;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class JurnalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Utama')
                    ->description('Detail waktu dan pengajar')
                    ->schema([
                        Select::make('tahun_ajaran_id')
                            ->relationship('tahunAjaran', 'nama', fn(Builder $query) => $query->where('is_active', true))
                            ->default(fn() => TahunAjaran::getActive()?->id)
                            ->required()
                            ->searchable(),

                        DatePicker::make('tanggal')
                            ->default(now())
                            ->required()
                            ->live(),

                        CheckboxList::make('jam_ke')
                            ->label('Jam Pelajaran')
                            ->options(function (Get $get, GeneralSettings $settings) {
                                $tanggal = $get('tanggal');
                                if (!$tanggal) return [];

                                // Mengambil nama hari berdasarkan tanggal terpilih
                                $hari = Carbon::parse($tanggal)->format('l');

                                // Mengambil jumlah jam dari GeneralSettings
                                $jumlahJam = $settings->jam_pelajaran_per_hari[$hari] ?? 0;

                                if ($jumlahJam <= 0) return [];

                                return collect(range(1, $jumlahJam))
                                    ->mapWithKeys(fn($i) => [$i => "Jam ke-{$i}"])
                                    ->toArray();
                            })
                            ->columns(2)
                            ->required()
                            ->bulkToggleable()
                            ->descriptions(function (Get $get, GeneralSettings $settings) {
                                $tanggal = $get('tanggal');
                                if (!$tanggal) return [];

                                $hari = Carbon::parse($tanggal)->format('l');
                                $jumlahJam = $settings->jam_pelajaran_per_hari[$hari] ?? 0;

                                // Mengembalikan array yang memetakan jam_ke ke deskripsi spesifik
                                return collect(range(1, $jumlahJam))
                                    ->mapWithKeys(fn($i) => [$i => "Durasi jam pelajaran ke-{$i}"])
                                    ->toArray();
                            }),
                    ]),

                Section::make('Detail Kegiatan Belajar Mengajar')
                    ->schema([
                        Select::make('guru_id')
                            ->relationship('guru', 'id')
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->user->name)
                            ->searchable()
                            ->default(fn() => Auth::user()->profileGuru?->id)
                            ->disabled(fn() => !Auth::user()->hasRole(['super_admin', 'admin']))
                            ->dehydrated() // Tetap dikirim ke database meski disabled
                            ->preload()
                            ->required(),

                        Grid::make(2)
                            ->schema([
                                Select::make('kelas_id')
                                    ->label('Kelas')
                                    ->options(function () {
                                        $user = Auth::user();
                                        $tahunAjaranAktif = TahunAjaran::getActive();

                                        if (!$tahunAjaranAktif) {
                                            return [];
                                        }

                                        // Jika Admin/Super Admin, tampilkan semua kelas aktif
                                        if ($user->hasAnyRole(['super_admin', 'admin'])) {
                                            return Kelas::active()->pluck('nama', 'id');
                                        }

                                        /** * Mengambil kelas dari tabel pivot guru_mengajar (jadwalMengajar)
                                         * yang sesuai dengan guru dan tahun ajaran aktif.
                                         */
                                        return GuruMengajar::query()
                                            ->where('guru_id', $user->profileGuru?->id)
                                            ->where('tahun_ajaran_id', $tahunAjaranAktif->id)
                                            ->where('is_active', true)
                                            ->with('kelas')
                                            ->get()
                                            ->pluck('kelas.nama', 'kelas_id')
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live() // Memastikan perubahan memicu reaktifitas jika diperlukan field lain
                                    ->afterStateUpdated(fn(Set $set) => $set('mapel_id', null)), // Reset mapel jika kelas berubah

                                Select::make('mapel_id')
                                    ->label('Mata Pelajaran')
                                    ->options(function (Get $get) {
                                        $kelasId = $get('kelas_id');
                                        $user = Auth::user();

                                        if (!$kelasId) return [];

                                        if ($user->hasAnyRole(['super_admin', 'admin'])) {
                                            return Mapel::active()->pluck('nama', 'id');
                                        }

                                        return GuruMengajar::query()
                                            ->where('guru_id', $user->profileGuru?->id)
                                            ->where('kelas_id', $kelasId)
                                            ->where('tahun_ajaran_id', TahunAjaran::getActive()?->id)
                                            ->with('mapel')
                                            ->get()
                                            ->pluck('mapel.nama', 'mapel_id');
                                    })
                                    ->required()
                                    ->searchable(),
                            ]),

                        TextInput::make('materi')
                            ->required()
                            ->placeholder('Contoh: Persamaan Linear Satu Variabel'),

                        Textarea::make('kegiatan')
                            ->required()
                            ->rows(4),
                    ]),

                Section::make('Absensi Siswa')
                    ->headerActions([
                        Action::make('panggilPresensi')
                            ->icon('heroicon-m-users')
                            ->color('success')
                            ->modalWidth('4xl')
                            ->fillForm(function (Get $schemaGet, ?Model $record = null) {
                                $kelasId = $schemaGet('kelas_id');
                                if (!$kelasId) {
                                    return ['temp_presensi' => []];
                                }

                                // 1. Ambil data absensi yang sudah tersimpan di record Jurnal (jika ada)
                                // Format di DB: ["Nama Siswa" => "Sakit (Izin Lomba)"]
                                $existingAbsensi = $record?->absensi ?? [];

                                $siswas = Siswa::query()
                                    ->with('user')
                                    ->active()
                                    ->whereHas('kelasSiswa', function ($q) use ($kelasId) {
                                        $q->where('kelas_id', $kelasId)->where('status', 'aktif');
                                    })
                                    ->orderBy(User::select('name')->whereColumn('users.id', 'siswas.user_id'))
                                    ->get();

                                $tempPresensi = $siswas->map(function ($siswa) use ($existingAbsensi) {
                                    $namaSiswa = $siswa->name;
                                    $statusRaw = $existingAbsensi[$namaSiswa] ?? 'Hadir';

                                    // 2. Pecah string "Status (Keterangan)" kembali ke komponennya
                                    // Contoh: "Sakit (Izin Lomba)" -> status: Sakit, keterangan: Izin Lomba
                                    preg_match('/^([^\(]+)(?:\s\((.*)\))?$/', $statusRaw, $matches);

                                    $status = trim($matches[1] ?? 'Hadir');
                                    $keterangan = $matches[2] ?? null;

                                    return [
                                        'siswa_id'   => $siswa->id,
                                        'nama_siswa' => $namaSiswa,
                                        'status'     => $status,
                                        'keterangan' => $keterangan,
                                    ];
                                })->values()->toArray();

                                return [
                                    'temp_presensi' => $tempPresensi,
                                ];
                            })
                            ->schema([
                                Repeater::make('temp_presensi')
                                    ->label('Daftar Presensi Siswa')
                                    ->itemLabel(
                                        fn(array $state): ?string => ($state['nama_siswa'] ?? 'Siswa') . " — [" . ($state['status'] ?? 'Hadir') . "]"
                                    )
                                    ->addable(false) // Guru tidak boleh tambah baris manual
                                    ->deletable(false) // Guru tidak boleh hapus baris
                                    ->reorderable(false)
                                    ->collapsed()
                                    ->columns(3) // Membuat layout menyamping (Siswa | Status | Keterangan)
                                    ->schema([
                                        Hidden::make('siswa_id')
                                            ->dehydrated(),

                                        TextInput::make('nama_siswa')
                                            ->label('Nama Siswa')
                                            ->disabled()
                                            ->dehydrated(),

                                        Select::make('status')
                                            ->label('Status')
                                            ->options([
                                                'Hadir' => 'Hadir',
                                                'Sakit' => 'Sakit',
                                                'Izin' => 'Izin',
                                                'Alpha' => 'Alpha',
                                            ])
                                            ->live()
                                            ->required()
                                            ->native(false),

                                        TextInput::make('keterangan')
                                            ->label('Catatan')
                                            ->placeholder('Contoh: Izin Lomba'),
                                    ]),
                            ])
                            ->action(function (array $data, Set $set) {
                                // Format: [ "Nama Siswa" => "Status (Keterangan)" ]
                                $formatted = collect($data['temp_presensi'])
                                    ->filter(fn($item) => $item['status'] !== 'Hadir') // Hanya ambil yang tidak hadir
                                    ->mapWithKeys(function ($item) {
                                        $statusFinal = $item['status'];
                                        if (!empty($item['keterangan'])) {
                                            $statusFinal .= " (" . $item['keterangan'] . ")";
                                        }
                                        return [$item['nama_siswa'] => $statusFinal];
                                    })
                                    ->toArray();

                                $set('absensi', $formatted);
                            }),
                    ])
                    ->schema([
                        KeyValue::make('absensi')
                            ->label('Ringkasan Ketidakhadiran')
                            ->keyLabel('Nama Siswa')
                            ->valueLabel('Keterangan')
                            ->editableValues(false)
                            ->editableKeys(false)
                            ->addable(false)
                            ->deletable(true)
                            ->helperText('Gunakan tombol "Panggil Presensi" di pojok kanan atas untuk input cepat.'),
                    ]),

                Section::make('Lampiran & Verifikasi')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('foto_kegiatan')
                            ->collection('foto_kegiatan')
                            ->multiple()
                            ->image()
                            ->columnSpanFull(),

                        Toggle::make('status_verifikasi')
                            ->label('Verifikasi Jurnal')
                            ->visible(fn() => Auth::user()->hasRole(['super_admin', 'admin']))
                            ->default(false),

                        Textarea::make('keterangan')
                            ->visible(fn() => Auth::user()->hasRole(['super_admin', 'admin']))
                            ->label('Catatan Tambahan'),
                    ]),
            ]);
    }
}
