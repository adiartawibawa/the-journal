<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{

    // Identitas
    public string $nama_sekolah;
    public string $nama_singkat;
    public string $npsn;
    public ?string $nss;
    public string $status_sekolah;

    // Kontak & Lokasi
    public string $alamat;
    public ?string $rt_rw;
    public string $kelurahan;
    public string $kecamatan;
    public string $kab_kota;
    public string $provinsi;
    public ?string $kode_pos;
    public ?string $telepon;
    public ?string $email;
    public ?string $website;

    // Akreditasi
    public string $akreditasi_status;
    public ?string $akreditasi_sk;
    public ?string $akreditasi_tahun;
    public ?string $akreditasi_tgl_kadaluarsa;

    // Pimpinan
    public string $nama_kepala_sekolah;
    public ?string $nip_kepala_sekolah;
    public ?string $ttd_digital;

    // Lembaga
    public ?string $nama_lembaga_naungan;
    public ?string $sk_pendirian_no;
    public ?string $sk_pendirian_tgl;

    // Tampilan
    public ?string $logo_sekolah;
    public string $kop_surat_template;
    public ?string $motto;

    public static function group(): string
    {
        return 'general';
    }
}
