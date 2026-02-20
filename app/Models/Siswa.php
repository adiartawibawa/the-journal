<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
    use HasUuids;

    protected $table = 'siswas';

    protected $fillable = [
        'user_id',
        'nisn',
        'tempat_lahir',
        'nama_ayah',
        'nama_ibu',
        'pekerjaan_orang_tua',
        'alamat_orang_tua',
        'no_telp_orang_tua',
        'tanggal_lahir',
        'is_active'
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
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
            ->orderBy('tanggal_mulai', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'tahun_ajaran' => $item->tahunAjaran?->nama,
                    'kelas' => $item->kelas?->nama,
                    'tingkat' => $item->kelas?->tingkat,
                    'jurusan' => $item->kelas?->jurusan,
                    'status' => $item->status,
                    'periode' => $item->periode,
                    'tanggal_mulai' => $item->tanggal_mulai?->format('d/m/Y'),
                    'tanggal_selesai' => $item->tanggal_selesai?->format('d/m/Y'),
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
        $awal = $this->kelasSiswa()->with('tahunAjaran')->orderBy('tanggal_mulai', 'asc')->first();
        return $awal ? $awal->tahunAjaran->nama : null;
    }

    // Mendapatkan Tahun Lulus secara dinamis
    public function getTahunLulusAttribute()
    {
        $akhir = $this->kelasSiswa()->with('tahunAjaran')->where('status', 'lulus')->first();
        return $akhir ? $akhir->tahunAjaran->nama : null;
    }
}
