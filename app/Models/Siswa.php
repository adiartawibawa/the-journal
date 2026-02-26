<?php

namespace App\Models;

use App\Traits\HasUserScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Siswa extends Model
{
    use HasUuids;

    protected $table = 'siswas';

    protected $fillable = [
        'user_id',
        'nisn',
        'tempat_lahir',
        'tanggal_lahir',
        'nama_ayah',
        'nama_ibu',
        'pekerjaan_orang_tua',
        'alamat_orang_tua',
        'no_telp_orang_tua',
        'is_active'
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'is_active' => 'boolean'
        ];
    }

    // Scope untuk siswa aktif
    public function scopeActive($query)
    {
        // Filter dasar: status is_active pada tabel siswas
        return $query->where('is_active', true);
    }

    // Method untuk menghitung total berdasarkan role (untuk Navigation Badge)
    public static function countSiswaAktifByRole(): int
    {
        $user = Auth::user();
        $query = self::query()->active(); // Memanggil scopeActive di atas

        if ($user && $user->hasRole('teacher')) {
            $guruId = $user->profileGuru?->id;

            // Filter agar hanya siswa di kelas perwalian yang aktif
            $query->whereHas('kelasSiswa', function ($q) use ($guruId) {
                $q->where('status', 'aktif') // Status di kelas tersebut aktif
                    ->whereHas('kelas.waliKelas', function ($wq) use ($guruId) {
                        $wq->where('guru_id', $guruId)
                            ->where('is_active', true); // Wali kelas masih menjabat
                    })
                    ->whereHas('tahunAjaran', fn($ta) => $ta->where('is_active', true));
            });
        }

        return $query->count();
    }

    // Relasi ke model user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke riwayat kelas
     */
    public function kelasSiswa(): HasMany
    {
        return $this->hasMany(KelasSiswa::class, 'siswa_id');
    }

    /**
     * Relasi ke riwayat status akademik (audit trail)
     */
    public function riwayatStatus(): HasMany
    {
        return $this->hasMany(RiwayatStatusSiswa::class, 'siswa_id')
            ->orderBy('created_at', 'desc');
    }

    public function riwayatAbsensi(): HasMany
    {
        return $this->hasMany(AbsensiSiswa::class, 'siswa_id');
    }

    /**
     * Mendapatkan kelas aktif siswa untuk tahun ajaran tertentu
     */
    public function getKelasAktif($tahunAjaranId = null)
    {
        $query = $this->kelasSiswa()
            ->with('kelas')
            ->where('status', 'aktif');

        if ($tahunAjaranId) {
            $query->where('tahun_ajaran_id', $tahunAjaranId);
        }

        return $query->first();
    }

    public static function getAbsensiStats($tahunAjaranId = null): array
    {
        // $user = Auth::user();
        // Query dasar Siswa sudah terfilter oleh UserDataScope jika Guru/Siswa
        $query = self::query()->active();

        // Filter berdasarkan Tahun Ajaran dari Page Filter
        if ($tahunAjaranId) {
            $query->whereHas('kelasSiswa', fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId));
        }

        // Ambil semua ID siswa yang masuk dalam scope user
        $siswaIds = $query->pluck('id');

        // Hitung status absensi dari tabel AbsensiSiswa
        $stats = AbsensiSiswa::whereIn('siswa_id', $siswaIds)
            ->when($tahunAjaranId, function ($q) use ($tahunAjaranId) {
                $q->whereHas('jurnal', fn($j) => $j->where('tahun_ajaran_id', $tahunAjaranId));
            })
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return [
            // 'Hadir' => $stats['Hadir'] ?? 0,
            'Sakit' => $stats['Sakit'] ?? 0,
            'Izin'  => $stats['Izin'] ?? 0,
            'Alpha' => $stats['Alpha'] ?? 0,
        ];
    }

    /**
     * Mendapatkan histori kelas siswa
     */
    public function getHistoriKelasAttribute()
    {
        return $this->kelasSiswa()
            ->with(['kelas', 'tahunAjaran'])
            ->orderBy('tanggal_masuk', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'tahun_ajaran' => $item->tahunAjaran?->nama,
                    'kelas' => $item->kelas?->nama,
                    'tingkat' => $item->kelas?->tingkat,
                    'jurusan' => $item->kelas?->jurusan,
                    'status' => $item->status,
                    'periode' => $item->periode,
                    'tanggal_masuk' => $item->tanggal_masuk?->format('d/m/Y'),
                    'tanggal_keluar' => $item->tanggal_keluar?->format('d/m/Y'),
                ];
            });
    }

    // Assesor
    public function getNameAttribute()
    {
        return $this->user?->name;
    }

    // Mendapatkan Tahun Masuk secara dinamis
    public function getTahunMasukAttribute()
    {
        $awal = $this->kelasSiswa()->with('tahunAjaran')->orderBy('tanggal_masuk', 'asc')->first();
        return $awal ? $awal->tahunAjaran->nama : null;
    }

    // Mendapatkan Tahun Lulus secara dinamis
    public function getTahunLulusAttribute()
    {
        $akhir = $this->kelasSiswa()->with('tahunAjaran')->where('status', 'lulus')->first();
        return $akhir ? $akhir->tahunAjaran->nama : null;
    }
}
