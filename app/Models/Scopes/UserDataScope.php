<?php

namespace App\Models\Scopes;

use App\Models\Kelas;
use App\Models\KelasSiswa;
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

            if (!$guruId) return;

            $tableName = $model->getTable();

            // Jika model memiliki kolom guru_id langsung (GuruMengajar, WaliKelas)
            if (Schema::hasColumn($tableName, 'guru_id')) {
                $builder->where('guru_id', $guruId);
            }

            // Isolasi untuk model Kelas
            elseif ($model instanceof Kelas) {
                $builder->where(function ($q) use ($guruId) {
                    // Filter: Kelas di mana guru tersebut adalah Wali Kelas
                    $q->whereHas('waliKelas', function ($query) use ($guruId) {
                        $query->where('guru_id', $guruId);
                    })
                        // ATAU Kelas di mana guru tersebut mengajar mata pelajaran
                        ->orWhereHas('guruMengajar', function ($query) use ($guruId) {
                            $query->where('guru_id', $guruId);
                        });
                });
            }

            // Isolasi untuk model KelasSiswa
            elseif ($model instanceof KelasSiswa) {
                $builder->whereHas('kelas', function ($q) use ($guruId) {
                    $q->whereHas('waliKelas', fn($query) => $query->where('guru_id', $guruId))
                        ->orWhereHas('guruMengajar', fn($query) => $query->where('guru_id', $guruId));
                });
            }
        }

        // Logika untuk Siswa
        elseif ($user->hasRole('student')) {
            $siswaId = $user->profileSiswa?->id;

            if (Schema::hasColumn($model->getTable(), 'siswa_id')) {
                $builder->where('siswa_id', $siswaId);
            }
        }
    }
}
