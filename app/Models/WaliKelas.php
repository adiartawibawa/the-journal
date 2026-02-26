<?php

namespace App\Models;

use App\Traits\HasUserScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaliKelas extends Model
{
    use HasUuids;
    use HasUserScope;

    protected $table = 'wali_kelas';

    protected $fillable = [
        'id',
        'tahun_ajaran_id',
        'kelas_id',
        'guru_id',
        'is_active'
    ];

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    protected static function booted()
    {
        static::saving(function ($waliKelas) {
            if ($waliKelas->is_active) {
                $conflict = static::where('tahun_ajaran_id', $waliKelas->tahun_ajaran_id)
                    ->where('kelas_id', $waliKelas->kelas_id)
                    ->where('is_active', true)
                    ->where('id', '!=', $waliKelas->id)
                    ->exists();

                if ($conflict) {
                    throw new \Exception("Kelas ini sudah memiliki Wali Kelas aktif.");
                }
            }
        });
    }
}
