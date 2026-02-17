<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\DB;

class KelasSiswa extends Pivot
{
    use HasUuids;

    protected $table = 'kelas_siswa';

    protected $fillable = [
        'id',
        'tahun_ajaran_id',
        'kelas_id',
        'siswa_id',
        'status',
        'tanggal_mulai',
        'tanggal_selesai',
        'keterangan'
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    // Accessor untuk display di Filament
    public function getPeriodeAttribute(): string
    {
        if ($this->tanggal_mulai && $this->tanggal_selesai) {
            return $this->tanggal_mulai->format('d/m/Y') . ' - ' .
                $this->tanggal_selesai->format('d/m/Y');
        }
        return '-';
    }

    // Scope untuk memudahkan query
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeByTahunAjaran($query, $tahunAjaranId)
    {
        return $query->where('tahun_ajaran_id', $tahunAjaranId);
    }

    public function scopeByKelas($query, $kelasId)
    {
        return $query->where('kelas_id', $kelasId);
    }

    public function scopeBySiswa($query, $siswaId)
    {
        return $query->where('siswa_id', $siswaId);
    }

    // Method untuk kenaikan kelas
    public function naikKelas(string $tahunAjaranBaruId, string $targetKelasId, ?string $keterangan = null)
    {
        $kelasBaru = Kelas::findOrFail($targetKelasId);

        // Validasi tetap dilakukan
        if ($kelasBaru->getJumlahSiswaAktifAttribute($tahunAjaranBaruId) >= $kelasBaru->kapasitas) {
            throw new \Exception("Kapasitas kelas {$kelasBaru->nama} penuh.");
        }

        // Update record lama (History)
        $this->update([
            'status' => 'lulus', // Atau 'naik_kelas' jika Anda menambah enum
            'tanggal_selesai' => now(),
        ]);

        // Insert record baru
        return self::create([
            'tahun_ajaran_id' => $tahunAjaranBaruId,
            'kelas_id' => $targetKelasId,
            'siswa_id' => $this->siswa_id,
            'status' => 'aktif',
            'tanggal_mulai' => now(),
        ]);
    }

    /**
     * Method untuk menangani siswa tinggal kelas
     */
    public function tinggalKelas(string $tahunAjaranBaruId, ?string $keterangan = null)
    {
        return DB::transaction(function () use ($tahunAjaranBaruId, $keterangan) {
            // 1. Update status record saat ini menjadi 'tinggal_kelas'
            // Pastikan Anda sudah menambah enum 'tinggal_kelas' di migration
            $this->update([
                'status' => 'tinggal_kelas',
                'tanggal_selesai' => now(),
                'keterangan' => $keterangan ?? 'Dinyatakan tinggal di kelas ' . $this->kelas->nama
            ]);

            // 2. Buat record baru untuk tahun ajaran baru di kelas yang SAMA
            return self::create([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'tahun_ajaran_id' => $tahunAjaranBaruId,
                'kelas_id' => $this->kelas_id, // Tetap di kelas yang sama
                'siswa_id' => $this->siswa_id,
                'status' => 'aktif',
                'tanggal_mulai' => now(),
                'keterangan' => 'Mengulang di kelas ' . $this->kelas->nama
            ]);
        });
    }

    // Scope untuk histori siswa
    public function scopeHistoriSiswa($query, $siswaId)
    {
        return $query->where('siswa_id', $siswaId)
            ->with(['kelas', 'tahunAjaran'])
            ->orderBy('tanggal_mulai', 'desc');
    }

    // Atau buat accessor untuk histori
    public function getHistoriAttribute()
    {
        return self::where('siswa_id', $this->siswa_id)
            ->with(['kelas', 'tahunAjaran'])
            ->orderBy('tanggal_mulai', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'tahun_ajaran' => $item->tahunAjaran?->nama,
                    'kelas' => $item->kelas?->nama,
                    'status' => $item->status,
                    'periode' => $item->periode,
                ];
            });
    }
}
