<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class UserDataScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (!Auth::check()) return;

        $user = Auth::user();

        // JIKA ADMIN ATAU SUPER ADMIN, LANGSUNG KELUAR DARI SCOPE (TANPA FILTER)
        if ($user->hasRole(['super_admin', 'admin'])) {
            return;
        }

        // Logika untuk Guru
        if ($user->hasRole('teacher')) {
            $guruId = $user->profileGuru?->id;
            // Cek apakah tabel model saat ini punya kolom 'guru_id'
            if (Schema::hasColumn($model->getTable(), 'guru_id')) {
                $builder->where('guru_id', $guruId);
            }
            // Khusus model Siswa dengan filter berdasarkan Wali Kelas
            elseif ($model->getTable() === 'siswas') {
                $builder->whereHas('kelasSiswa.kelas.waliKelas', function ($query) use ($guruId) {
                    $query->where('guru_id', $guruId)
                        ->where('is_active', true);
                });
            }
            // Khusus untuk Model Kelas yang tidak punya 'guru_id' langsung
            elseif ($model->getTable() === 'kelas') {
                $builder->whereHas('waliKelas', fn($q) => $q->where('guru_id', $guruId));
            }
        }

        // Logika untuk Siswa
        elseif ($user->hasRole('student')) {
            $siswaId = $user->profileSiswa?->id;

            if (Schema::hasColumn($model->getTable(), 'siswa_id')) {
                $builder->where('siswa_id', $siswaId);
            } elseif ($model->getTable() === 'kelas') {
                $builder->whereHas('kelasSiswa', fn($q) => $q->where('siswa_id', $siswaId));
            }
        }
    }
}
