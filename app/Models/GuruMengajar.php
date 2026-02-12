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
}
