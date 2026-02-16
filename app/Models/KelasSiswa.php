<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class KelasSiswa extends Model
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
    public function naikKelas(string $tahunAjaranBaruId, ?string $keterangan = null)
    {
        // Cari kelas dengan tingkat selanjutnya
        $kelasBaru = Kelas::where('tingkat', $this->kelas->tingkat + 1)
            ->where('jurusan', $this->kelas->jurusan)
            ->where('is_active', true)
            ->first();

        if (!$kelasBaru) {
            throw new \Exception('Kelas tujuan tidak ditemukan untuk tingkat ' . ($this->kelas->tingkat + 1));
        }

        // Cek kapasitas kelas baru
        $jumlahSiswaBaru = self::where('kelas_id', $kelasBaru->id)
            ->where('tahun_ajaran_id', $tahunAjaranBaruId)
            ->where('status', 'aktif')
            ->count();

        if ($jumlahSiswaBaru >= $kelasBaru->kapasitas) {
            throw new \Exception("Kelas {$kelasBaru->nama} sudah mencapai kapasitas maksimal");
        }

        // Update status kelas lama
        $this->update([
            'status' => 'lulus',
            'tanggal_selesai' => now(),
            'keterangan' => $keterangan ?? 'Naik kelas ke ' . $kelasBaru->nama
        ]);

        // Buat record baru di kelas baru
        return self::create([
            'id' => (string) Str::uuid(),
            'tahun_ajaran_id' => $tahunAjaranBaruId,
            'kelas_id' => $kelasBaru->id,
            'siswa_id' => $this->siswa_id,
            'status' => 'aktif',
            'tanggal_mulai' => now(),
            'keterangan' => $keterangan ?? 'Naik kelas dari ' . $this->kelas->nama
        ]);
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
