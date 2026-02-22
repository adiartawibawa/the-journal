<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait HasMultitenancy
{
    /**
     * Boot trait untuk mengaplikasikan Global Scope secara otomatis.
     */
    protected static function bootHasMultitenancy(): void
    {
        static::addGlobalScope('multitenancy_scope', function (Builder $builder) {
            if (Auth::check()) {
                $user = Auth::user();

                // Jika Guru: Hanya lihat data miliknya sendiri
                if ($user->hasRole('teacher')) {
                    // Pastikan model yang menggunakan trait ini memiliki kolom 'guru_id'
                    // atau sesuaikan dengan foreign key di model tersebut
                    $builder->where('guru_id', $user->profileGuru?->id);
                }

                // Jika Siswa: Hanya lihat data di kelasnya yang aktif
                if ($user->hasRole('student')) {
                    $kelasId = $user->profileSiswa?->kelasSiswa()
                        ->where('status', 'aktif')
                        ->first()?->kelas_id;

                    $builder->where('kelas_id', $kelasId);
                }

                // Admin & Super Admin: Tidak difilter (melihat semua data)
            }
        });
    }
}
