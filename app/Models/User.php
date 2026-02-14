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
use Spatie\MediaLibrary\MediaCollections\Models\Media;
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

    // Register media collections
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile() // Hanya boleh 1 file per user
            ->acceptsMimeTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp'])
            ->registerMediaConversions(function (Media $media = null) {
                $this->addMediaConversion('thumb')
                    ->width(100)
                    ->height(100)
                    ->sharpen(10);

                $this->addMediaConversion('medium')
                    ->width(300)
                    ->height(300)
                    ->sharpen(5);

                $this->addMediaConversion('large')
                    ->width(800)
                    ->height(800)
                    ->sharpen(0);
            });
    }

    // Helper method untuk mendapatkan URL avatar
    public function getAvatarUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('avatar');

        if ($media) {
            return $media->getUrl();
        }

        // Return default avatar based on email/name
        return $this->getDefaultAvatarUrl();
    }

    // Helper method untuk mendapatkan thumbnail avatar
    public function getAvatarThumbUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('avatar');

        if ($media && $media->hasGeneratedConversion('thumb')) {
            return $media->getUrl('thumb');
        }

        return $this->getAvatarUrlAttribute();
    }

    // Helper method untuk mendapatkan avatar ukuran medium
    public function getAvatarMediumUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('avatar');

        if ($media && $media->hasGeneratedConversion('medium')) {
            return $media->getUrl('medium');
        }

        return $this->getAvatarUrlAttribute();
    }

    // Default avatar menggunakan UI Avatars or Gravatar
    protected function getDefaultAvatarUrl(): string
    {
        // // Menggunakan UI Avatars (https://ui-avatars.com)
        // $name = urlencode($this->name);
        // return "https://ui-avatars.com/api/?name={$name}&color=7F9CF5&background=EBF4FF&size=256&bold=true";

        // Alternatif menggunakan Gravatar
        $hash = md5(strtolower(trim($this->email)));
        return "https://www.gravatar.com/avatar/{$hash}?s=256&d=mp";
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
