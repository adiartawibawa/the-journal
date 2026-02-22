<?php

namespace App\Models;

use App\Enums\Semester;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
            'semester' => Semester::class,
            'tanggal_awal' => 'date',
            'tanggal_akhir' => 'date',
            'is_active' => 'boolean'
        ];
    }

    protected static $activeCache = null;

    /**
     * Relasi ke kelas_siswa
     */
    public function kelasSiswa(): HasMany
    {
        return $this->hasMany(KelasSiswa::class, 'tahun_ajaran_id');
    }

    public function jurnals(): HasMany
    {
        return $this->hasMany(Jurnal::class);
    }

    public function isSemesterGenap(): bool
    {
        return $this->semester === Semester::Genap;
    }

    public function isSemesterGanjil(): bool
    {
        return $this->semester === Semester::Ganjil;
    }

    /**
     * Mengecek apakah Semester saat ini adalah Genap
     */
    public static function currentIsGenap(): bool
    {
        return static::getActive()?->isSemesterGenap() ?? false;
    }

    /**
     * Mengecek apakah Semester saat ini adalah Ganjil
     */
    public static function currentIsGanjil(): bool
    {
        return static::getActive()?->isSemesterGanjil() ?? false;
    }

    protected function namaSemester(): Attribute
    {
        return Attribute::make(
            get: fn() => "{$this->nama} - Semester {$this->semester->getLabel()}",
        );
    }

    // Method untuk mengaktifkan tahun ajaran ini dan menonaktifkan lainnya
    public function activate()
    {
        // Set is_active ke true dan simpan.
        // Hook 'saving' di bawah yang akan bekerja menonaktifkan record lain.
        $this->is_active = true;
        $this->save();
    }

    /**
     * Helper internal untuk mengambil data Tahun Ajaran yang aktif (Memoized)
     */
    public static function getActive(): ?static
    {
        if (static::$activeCache === null) {
            static::$activeCache = static::where('is_active', true)->first();
        }

        return static::$activeCache;
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
