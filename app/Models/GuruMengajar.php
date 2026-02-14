<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class GuruMengajar extends Pivot
{
    use HasUuids;

    // Casting agar tipe data integer tetap konsisten
    protected $casts = [
        'kkm' => 'integer',
        'jam_per_minggu' => 'integer',
        'is_active' => 'boolean',
    ];

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    // Scope untuk filter berdasarkan tahun ajaran aktif
    public function scopeTahunAjaranAktif($query)
    {
        return $query->whereHas('tahunAjaran', function ($q) {
            $q->where('is_active', true);
        });
    }

    // Scope untuk filter berdasarkan status mengajar aktif
    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    // Assesor
    public function getGuruMengajarAttribute()
    {
        return $this->guru->name . ' - ' . $this->mapel->nama;
    }
}
