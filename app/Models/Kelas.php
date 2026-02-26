<?php

namespace App\Models;

use App\Traits\HasUserScope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Auth;

class Kelas extends Model
{
    use HasUuids;
    use HasUserScope;

    protected $table = 'kelas';

    protected $fillable = [
        'kode',
        'nama',
        'tingkat',
        'jurusan',
        'kapasitas',
        'deskripsi',
        'is_active'
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // Scope untuk kelas aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Relasi ke kelas_siswa
     */
    public function kelasSiswa(): HasMany
    {
        return $this->hasMany(KelasSiswa::class, 'kelas_id');
    }

    /**
     * Akses langsung ke daftar Siswa melalui tabel perantara KelasSiswa.
     * Format: HasManyThrough(Target, Perantara, foreignKeyPerantara, foreignKeyTarget, localKeyKelas, localKeyPerantara)
     */
    public function siswas(): HasManyThrough
    {
        return $this->hasManyThrough(
            Siswa::class,
            KelasSiswa::class,
            'kelas_id',    // Foreign key di tabel kelas_siswa
            'id',          // Foreign key di tabel siswa (ID siswa itu sendiri)
            'id',          // Local key di tabel kelas
            'siswa_id'     // Local key di tabel kelas_siswa
        );
    }

    // Relasi: Satu kelas memiliki banyak entri jurnal (dari berbagai mapel)
    public function jurnals(): HasMany
    {
        return $this->hasMany(Jurnal::class);
    }

    public function waliKelas(): HasMany
    {
        return $this->hasMany(WaliKelas::class, 'kelas_id');
    }

    public function guruMengajar(): HasMany
    {
        return $this->hasMany(GuruMengajar::class, 'kelas_id');
    }

    /**
     * Digunakan untuk Badge Navigasi (Menghitung total kelas perwalian)
     */
    public static function totalKelasPerwalian(): int
    {
        $user = Auth::user();
        $query = self::query()->active();

        // Jika user adalah guru, batasi hanya kelas yang ia pimpin (Wali Kelas)
        if ($user && $user->hasRole('teacher')) {
            $guruId = $user->profileGuru?->id;

            $query->whereHas('waliKelas', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId)
                    ->whereHas('tahunAjaran', fn($ta) => $ta->where('is_active', true));
            });
        }

        // Jika Admin/Super Admin, ini akan mengembalikan jumlah semua kelas aktif
        return $query->count();
    }

    /**
     * Digunakan untuk Badge Navigasi (Menghitung total siswa di semua kelas)
     */
    public static function totalSiswaAktif(): int
    {
        $user = Auth::user();
        $query = self::query();

        // Jika user adalah guru, paksa filter hanya untuk perwalian saja
        if ($user->hasRole('teacher')) {
            $guruId = $user->profileGuru?->id;

            $query->whereHas('waliKelas', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId)
                    ->whereHas('tahunAjaran', fn($ta) => $ta->where('is_active', true));
            });
        }

        return (int) $query
            ->withCount(['kelasSiswa as total' => function ($q) {
                $q->where('status', 'aktif')
                    ->whereHas('tahunAjaran', fn($ta) => $ta->where('is_active', true));
            }])
            ->get()
            ->sum('total');
    }

    /**
     * ACCESSOR (Attribute)
     * Digunakan jika memanggil $kelas->jumlah_siswa_aktif pada instansi tunggal
     */
    public function getJumlahSiswaAktifAttribute(): int
    {
        return $this->kelasSiswa()
            ->where('status', 'aktif')
            ->whereHas('tahunAjaran', fn($q) => $q->where('is_active', true))
            ->count();
    }

    // Accessor untuk menghitung kapasitas tersisa
    public function getSisaKapasitasAttribute(): int
    {
        $aktif = $this->kelasSiswa()
            ->where('status', 'aktif')
            ->whereHas('tahunAjaran', fn($q) => $q->where('is_active', true))
            ->count();

        return max(0, $this->kapasitas - $aktif);
    }

    /**
     * Accessor untuk Nama Kelas + Tahun Ajaran Aktif
     * Format: "2024/2025 - XI RPL 1"
     */
    protected function namaKelasTa(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Mengambil tahun ajaran aktif melalui relasi kelasSiswa
                $tahunAjaran = $this->kelasSiswa()
                    ->whereHas('tahunAjaran', fn($q) => $q->where('is_active', true))
                    ->first()
                    ?->tahunAjaran;

                if (!$tahunAjaran) {
                    return $this->nama;
                }

                return "{$tahunAjaran->nama} - {$this->nama}";
            }
        );
    }
}
