<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mapel extends Model
{
    use HasUuids;

    protected $table = 'mapels';

    protected $fillable = [
        'kode',
        'nama',
        'kelompok',
        'is_active'
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function daftarPengajar(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'guru_mengajar', 'mapel_id', 'user_id')
            ->using(GuruMengajar::class)
            ->withPivot('id', 'tahun_ajaran_id', 'kelas_id', 'kkm')
            ->withTimestamps();
    }

    public function jurnals(): HasMany
    {
        return $this->hasMany(Jurnal::class);
    }
}
