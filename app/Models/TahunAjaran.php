<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    use HasUuids;

    protected $fillable = [
        'nama',
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
}
