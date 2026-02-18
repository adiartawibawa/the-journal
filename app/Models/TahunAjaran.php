<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class TahunAjaran extends Model
{
    use HasUuids;

    protected $table = 'tahun_ajarans';

    protected $fillable = [
        'nama',
        'tanggal_awal',
        'tanggal_akhir',
        'semester',
        'is_active'
    ];

    protected function casts()
    {
        return [
            'tanggal_awal' => 'date',
            'tanggal_akhir' => 'date',
            'is_active' => 'boolean'
        ];
    }

    /**
     * Relasi ke kelas_siswa
     */
    public function kelasSiswa(): HasMany
    {
        return $this->hasMany(KelasSiswa::class, 'tahun_ajaran_id');
    }

    // Method untuk mengaktifkan tahun ajaran ini dan menonaktifkan lainnya
    public function activate()
    {
        // Set is_active ke true dan simpan.
        // Hook 'saving' di bawah yang akan bekerja menonaktifkan record lain.
        $this->is_active = true;
        $this->save();
    }

    protected static function booted(): void
    {
        static::saving(function (TahunAjaran $tahunAjaran) {
            // Jika tahun ajaran ini diset aktif (is_active = true)
            if ($tahunAjaran->is_active) {
                // Nonaktifkan semua tahun ajaran lain sebelum menyimpan record ini
                static::query()
                    ->where('id', '!=', $tahunAjaran->id)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }
        });
    }
}
