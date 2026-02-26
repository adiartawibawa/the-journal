<?php

namespace App\Models;

use App\Traits\HasUserScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KelasSiswa extends Model
{
    use HasUuids;
    use HasUserScope;

    protected $table = 'kelas_siswa';

    protected $fillable = [
        'id',
        'tahun_ajaran_id',
        'kelas_id',
        'siswa_id',
        'status',
        'hasil_akhir',
        'tanggal_masuk',
        'tanggal_keluar',
        'catatan_internal'
    ];

    protected function casts(): array
    {
        return [
            'tanggal_masuk' => 'date',
            'tanggal_keluar' => 'date',
        ];
    }

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
        if ($this->tanggal_masuk && $this->tanggal_keluar) {
            return $this->tanggal_masuk->format('d/m/Y') . ' - ' .
                $this->tanggal_keluar->format('d/m/Y');
        }
        return $this->tanggal_masuk ? $this->tanggal_masuk->format('d/m/Y') . ' - Aktif' : '-';
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

    // --- Core Business Logic ---

    /**
     * Memproses transisi siswa ke semester berikutnya dalam satu tahun ajaran.
     */
    public function lanjutSemester(string $semesterBaruId)
    {
        return DB::transaction(function () use ($semesterBaruId) {
            // Cek apakah sudah terdaftar di semester tersebut
            $exists = self::where('tahun_ajaran_id', $semesterBaruId)
                ->where('siswa_id', $this->siswa_id)
                ->exists();

            if ($exists) {
                return null; // Abaikan jika sudah ada
            }

            // Update record semester saat ini (semester lama)
            // Kita nyatakan 'keluar' dari semester ini dengan hasil 'lanjut'
            $this->update([
                'status' => 'keluar',
                'hasil_akhir' => 'lanjut',
                'tanggal_keluar' => now(),
                'catatan_internal' => ($this->catatan_internal ? $this->catatan_internal . ' | ' : '') . 'Selesai semester, lanjut ke semester berikutnya.'
            ]);

            // Buat record baru untuk semester baru namun di KELAS YANG SAMA
            return self::create([
                'id' => (string) Str::uuid(),
                'tahun_ajaran_id' => $semesterBaruId,
                'kelas_id' => $this->kelas_id,
                'siswa_id' => $this->siswa_id,
                'status' => 'aktif',
                'tanggal_masuk' => now(),
                'catatan_internal' => 'Lanjut dari semester sebelumnya.',
            ]);
        });
    }

    // Method untuk kenaikan kelas
    public function naikKelas(string $tahunAjaranBaruId, string $targetKelasId)
    {
        return DB::transaction(function () use ($tahunAjaranBaruId, $targetKelasId) {
            // Update record saat ini (menjadi history)
            $this->update([
                'status' => 'keluar',
                'hasil_akhir' => 'naik_kelas',
                'tanggal_keluar' => now(),
            ]);

            // Buat Record Kelas Baru
            return self::create([
                'tahun_ajaran_id' => $tahunAjaranBaruId,
                'kelas_id' => $targetKelasId,
                'siswa_id' => $this->siswa_id,
                'status' => 'aktif',
                'tanggal_masuk' => now(),
            ]);
        });
    }

    /**
     * Method untuk menangani siswa tinggal kelas
     */
    public function tinggalKelas(string $tahunAjaranBaruId, ?string $keterangan = null)
    {
        return DB::transaction(function () use ($tahunAjaranBaruId, $keterangan) {
            // Update record saat ini sebagai history tahun lalu
            $this->update([
                'status' => 'keluar',
                'hasil_akhir' => 'tinggal_kelas',
                'tanggal_keluar' => now(),
                'catatan_internal' => $keterangan ?? 'Siswa dinyatakan tinggal kelas.'
            ]);

            // Buat Record Baru (Kelas yang sama)
            return self::create([
                'tahun_ajaran_id' => $tahunAjaranBaruId,
                'kelas_id' => $this->kelas_id,
                'siswa_id' => $this->siswa_id,
                'status' => 'aktif',
                'tanggal_masuk' => now(),
                'catatan_internal' => 'Mengulang di kelas yang sama.'
            ]);
        });
    }

    // Scope untuk histori siswa
    public function scopeHistoriSiswa($query, $siswaId)
    {
        return $query->where('siswa_id', $siswaId)
            ->with(['kelas', 'tahunAjaran'])
            ->orderBy('tanggal_masuk', 'desc');
    }

    // Atau buat accessor untuk histori
    public function getHistoriAttribute()
    {
        return self::where('siswa_id', $this->siswa_id)
            ->with(['kelas', 'tahunAjaran'])
            ->orderBy('tanggal_masuk', 'desc')
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
