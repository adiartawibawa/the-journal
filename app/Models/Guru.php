<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guru extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'nuptk',
        'status_kepegawaian',
        'bidang_studi',
        'golongan',
        'tanggal_masuk',
        'pendidikan_terakhir'
    ];

    protected function casts(): array
    {
        return [
            'tanggal_masuk' => 'date',
        ];
    }

    // Relasi ke model user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Guru sebagai wali kelas
    // Relasi ke Wali Kelas (Pivot)
    public function waliKelas(): HasMany
    {
        return $this->hasMany(WaliKelas::class, 'user_id', 'user_id');
    }

    // Relasi ke Guru Mengajar (Pivot)
    public function tugasMengajar(): HasMany
    {
        return $this->hasMany(GuruMengajar::class, 'user_id', 'user_id');
    }

    // assesor
    public function getNameAttribute()
    {
        return $this->user?->name;
    }
}
