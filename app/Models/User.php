<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use HasUuids;
    use SoftDeletes;
    use HasRoles;
    use InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // User sebagai guru
    public function profileGuru(): HasOne
    {
        return $this->hasOne(Guru::class, 'user_id');
    }

    // User sebagai siswa
    public function profileSiswa(): HasOne
    {
        return $this->hasOne(Siswa::class, 'user_id');
    }

    public function daftarWaliKelas()
    {
        return $this->belongsToMany(Kelas::class, 'wali_kelas', 'user_id', 'kelas_id')
            ->using(WaliKelas::class) // Wajib didefinisikan
            ->withPivot('id', 'tahun_ajaran_id', 'is_active')
            ->withTimestamps();
    }

    public function jadwalMengajar()
    {
        return $this->belongsToMany(Mapel::class, 'guru_mengajar', 'user_id', 'mapel_id')
            ->using(GuruMengajar::class) // Wajib didefinisikan
            ->withPivot('id', 'kelas_id', 'tahun_ajaran_id', 'kkm', 'jam_per_minggu')
            ->withTimestamps();
    }

    // Relasi ke Riwayat Kelas (Data Dinamis: Tahun ke Tahun)
    public function riwayatKelas()
    {
        return $this->belongsToMany(Kelas::class, 'kelas_siswa', 'user_id', 'kelas_id')
            ->using(KelasSiswa::class)
            ->withPivot('id', 'tahun_ajaran_id', 'status')
            ->withTimestamps();
    }

    // Helper untuk mendapatkan kelas siswa yang AKTIF saat ini
    public function kelasSekarang()
    {
        return $this->riwayatKelas()
            ->wherePivot('status', 'aktif')
            ->latest();
    }
}
