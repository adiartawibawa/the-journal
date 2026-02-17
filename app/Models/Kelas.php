<?php

namespace App\Models;

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
     * Mendapatkan jumlah siswa aktif di kelas ini untuk tahun ajaran tertentu
     */
    public function getJumlahSiswaAktifAttribute($tahunAjaranId = null)
    {
        $query = $this->kelasSiswa()->where('status', 'aktif');

        if ($tahunAjaranId) {
            $query->where('tahun_ajaran_id', $tahunAjaranId);
        }

        return $query->count();
    }
}
