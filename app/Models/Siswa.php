<?php

namespace App\Models;

use App\Traits\HasUserScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
    use HasUuids;
    use HasUserScope;

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
        return $query->where('is_active', true);
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
