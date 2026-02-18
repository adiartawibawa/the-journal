<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    use HasUuids;

    protected $table = 'kelas';

    protected $fillable = [
        'kode',
        'nama',
        'tingkat',
        'jurusan',
        'kapasitas',
        'deskripsi',
        'is_active',
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
     * METHOD STATIC
     * Digunakan untuk Badge Navigasi (Menghitung total siswa di semua kelas)
     */
    public static function totalSiswaAktif()
    {
        return self::query()
            ->whereHas('kelasSiswa', function ($query) {
                $query->where('status', 'aktif')
                    ->whereHas('tahunAjaran', fn($q) => $q->where('is_active', true));
            })
            ->withCount(['kelasSiswa as total' => function ($query) {
                $query->where('status', 'aktif')
                    ->whereHas('tahunAjaran', fn($q) => $q->where('is_active', true));
            }])
            ->get()
            ->sum('total');
    }

    /**
     * ACCESSOR (Attribute)
     * Digunakan jika Anda memanggil $kelas->jumlah_siswa_aktif pada instansi tunggal
     */
    public function getJumlahSiswaAktifAttribute(): int
    {
        return $this->kelasSiswa()
            ->where('status', 'aktif')
            ->whereHas('tahunAjaran', fn($q) => $q->where('is_active', true))
            ->count();
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
