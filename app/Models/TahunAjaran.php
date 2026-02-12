<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TahunAjaran extends Model
{
    use HasUuids;

    protected $fillable = [
        'nama',
        'tanggal_awal',
        'tanggal_akhir',
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

    // Method untuk mengaktifkan tahun ajaran ini dan menonaktifkan lainnya
    public function activate()
    {
        DB::transaction(function () {
            self::where('id', '!=', $this->id)->update(['is_active' => false]);
            $this->update(['is_active' => true]);
        });
    }
}
