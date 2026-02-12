<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Siswa extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'nisn',
        'tempat_lahir',
        'nama_ayah',
        'nama_ibu',
        'pekerjaan_orang_tua',
        'alamat_orang_tua',
        'no_telp_orang_tua',
        'is_active'
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
        ];
    }

    // Scope untuk siswa aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Relasi ke model user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function riwayatKelas()
    {
        return $this->hasMany(KelasSiswa::class, 'user_id', 'user_id');
    }
}
