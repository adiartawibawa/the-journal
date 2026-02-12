<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasUuids;

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
}
