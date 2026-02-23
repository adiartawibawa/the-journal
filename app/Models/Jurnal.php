<?php

namespace App\Models;

use App\Traits\HasMultitenancy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Jurnal extends Model implements HasMedia
{
    use HasUuids, SoftDeletes;
    use InteractsWithMedia;
    use HasMultitenancy;

    protected $fillable = [
        'tahun_ajaran_id',
        'guru_id',
        'mapel_id',
        'kelas_id',
        'tanggal',
        'jam_ke',
        'materi',
        'kegiatan',
        'absensi',
        'keterangan',
        'status_verifikasi'
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jam_ke' => 'array',
            'absensi' => 'array',
            'status_verifikasi' => 'boolean'
        ];
    }

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('foto_kegiatan')
            ->acceptsMimeTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp'])
            ->registerMediaConversions(function (Media $media = null) {
                $this->addMediaConversion('thumb')
                    ->width(200)
                    ->height(200)
                    ->sharpen(10);

                $this->addMediaConversion('medium')
                    ->width(800)
                    ->height(600)
                    ->sharpen(5);
            });
    }

    /**
     * Helper untuk mendapatkan URL foto kegiatan
     */
    public function getFotoKegiatanUrlsAttribute(): array
    {
        return $this->getMedia('foto_kegiatan')->map(function ($media) {
            return [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'thumb' => $media->getUrl('thumb'),
                'medium' => $media->getUrl('medium'),
                'name' => $media->file_name,
                'size' => $media->size,
                'created_at' => $media->created_at->format('d/m/Y H:i')
            ];
        })->toArray();
    }

    /**
     * Helper untuk mendapatkan foto kegiatan pertama
     */
    public function getFirstFotoKegiatanUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('foto_kegiatan');
    }

    // --- Relasi ---

    // Relasi ke Tahun Ajaran aktif
    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    // Relasi ke Guru yang mengajar
    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    // Relasi ke Mata Pelajaran
    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class);
    }

    // Relasi ke Kelas tempat mengajar
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function absensiSiswas(): HasMany
    {
        return $this->hasMany(AbsensiSiswa::class, 'jurnal_id');
    }

    /**
     * Scope untuk filter berdasarkan tahun ajaran aktif
     */
    public function scopeForActiveTahunAjaran($query)
    {
        return $query->whereHas('tahunAjaran', function ($q) {
            $q->where('is_active', true);
        });
    }

    /**
     * Hitung total jurnal per guru
     */
    public static function countByGuru($guruId = null, $tahunAjaranId = null)
    {
        $query = self::query();

        if ($guruId) {
            $query->where('guru_id', $guruId);
        }

        if ($tahunAjaranId) {
            $query->where('tahun_ajaran_id', $tahunAjaranId);
        } else {
            $query->whereHas('tahunAjaran', fn($q) => $q->where('is_active', true));
        }

        return $query->count();
    }
}
