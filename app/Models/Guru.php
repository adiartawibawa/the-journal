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

    protected $table = 'gurus';

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
    public function waliKelas(): HasMany
    {
        // Gunakan guru_id sebagai foreign key, bukan user_id
        return $this->hasMany(WaliKelas::class, 'guru_id', 'id');
    }

    // Relasi ke Guru Mengajar
    public function tugasMengajar(): HasMany
    {
        // Gunakan guru_id sebagai foreign key
        return $this->hasMany(GuruMengajar::class, 'guru_id', 'id');
    }

    // assesor
    public function getNameAttribute()
    {
        return $this->user?->name;
    }
}
