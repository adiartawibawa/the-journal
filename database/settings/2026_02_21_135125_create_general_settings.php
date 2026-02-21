<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.nama_sekolah', 'Nama Sekolah Default');
        $this->migrator->add('general.nama_singkat', 'NSD');
        $this->migrator->add('general.npsn', '');
        $this->migrator->add('general.nss', '');
        $this->migrator->add('general.status_sekolah', 'Negeri'); // Negeri/Swasta

        // Kontak & Lokasi
        $this->migrator->add('general.alamat', '');
        $this->migrator->add('general.rt_rw', '');
        $this->migrator->add('general.kelurahan', '');
        $this->migrator->add('general.kecamatan', '');
        $this->migrator->add('general.kab_kota', '');
        $this->migrator->add('general.provinsi', '');
        $this->migrator->add('general.kode_pos', '');
        $this->migrator->add('general.telepon', '');
        $this->migrator->add('general.email', '');
        $this->migrator->add('general.website', '');

        // Akreditasi
        $this->migrator->add('general.akreditasi_status', 'A');
        $this->migrator->add('general.akreditasi_sk', '');
        $this->migrator->add('general.akreditasi_tahun', '');
        $this->migrator->add('general.akreditasi_tgl_kadaluarsa', '');

        // Pimpinan
        $this->migrator->add('general.nama_kepala_sekolah', '');
        $this->migrator->add('general.nip_kepala_sekolah', '');
        $this->migrator->add('general.ttd_digital', ''); // Path gambar

        // Lembaga
        $this->migrator->add('general.nama_lembaga_naungan', '');
        $this->migrator->add('general.sk_pendirian_no', '');
        $this->migrator->add('general.sk_pendirian_tgl', '');

        // Tampilan
        $this->migrator->add('general.logo_sekolah', '');
        $this->migrator->add('general.kop_surat_template', 'default');
        $this->migrator->add('general.motto', '');
    }
};
