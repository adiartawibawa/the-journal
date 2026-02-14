<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Imports\UserImporter;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah User')
                ->icon('heroicon-o-plus'),

            ImportAction::make()
                ->label('Import Users')
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->color('danger')
                ->importer(UserImporter::class)
                ->maxRows(1000)
                ->chunkSize(100)
                ->csvDelimiter(',')
                ->modalHeading('Import Data Users')
                ->modalDescription('Upload file CSV dengan data users. Download template untuk format yang benar.'),

            Action::make('download_template')
                ->label('')
                ->icon(Heroicon::OutlinedDocumentArrowDown)
                ->color('gray')
                ->schema([
                    Select::make('template_type')
                        ->label('Pilih template')
                        ->options([
                            'student' => 'Template untuk Murid (Siswa)',
                            'teacher' => 'Template untuk Guru',
                            'all' => 'Template Umum (All Users)',
                        ])
                        ->default('all')
                        ->required()
                        ->native(false)
                        ->helperText('Pilih template sesuai dengan role user yang akan diimport'),
                ])
                ->action(function (array $data) {
                    // Panggil method download berdasarkan pilihan
                    return $this->downloadTemplate($data['template_type']);
                })
                ->modalHeading('Download Template Import')
                ->modalSubmitActionLabel('Download')
                ->modalCancelActionLabel('Batal'),
        ];
    }

    /**
     * Download template berdasarkan tipe
     */
    protected function downloadTemplate(string $type): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = $this->getTemplateHeaders($type);
        $example = $this->getExampleData($type);
        $filename = $this->getTemplateFilename($type);
        $instructions = $this->getTemplateInstructions($type);

        return $this->generateCsvTemplate($headers, $example, $filename, $instructions);
    }

    /**
     * Generate CSV template
     */
    protected function generateCsvTemplate(
        array $headers,
        array $example,
        string $filename,
        array $instructions = []
    ): StreamedResponse {
        $callback = function () use ($headers, $example, $instructions) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for Excel
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Write headers
            fputcsv($file, $headers);

            // Write example row
            fputcsv($file, $example);

            // Write empty template rows
            fputcsv($file, array_fill(0, count($headers), ''));
            fputcsv($file, array_fill(0, count($headers), ''));

            // Add instructions
            fputcsv($file, ['# ========== INSTRUKSI IMPORT ==========']);
            fputcsv($file, ['# 1. Jangan hapus atau ubah baris header (baris pertama)']);
            fputcsv($file, ['# 2. Isi data mulai baris ke-3 (setelah contoh)']);
            fputcsv($file, ['# 3. Kolom dengan tanda * (asterisk) wajib diisi']);
            fputcsv($file, ['# 4. Password: Kosongkan untuk auto-generate, atau isi dengan password plain']);
            fputcsv($file, ['# 5. Format tanggal: YYYY-MM-DD (contoh: 2024-01-15)']);

            foreach ($instructions as $instruction) {
                fputcsv($file, ['# ' . $instruction]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }

    /**
     * Get headers berdasarkan tipe template (Disesuaikan dengan model)
     */
    protected function getTemplateHeaders(string $type): array
    {
        // Base headers untuk tabel users
        $baseHeaders = [
            'name*',
            'email*',
            'password',
            'email_verified_at',
            'roles'
        ];

        return match ($type) {
            'student' => array_merge($baseHeaders, [
                'nisn*',              // wajib untuk siswa
                'tempat_lahir',
                'tanggal_lahir*',
                'nama_ayah',
                'nama_ibu',
                'pekerjaan_orang_tua',
                'alamat_orang_tua',
                'no_telp_orang_tua',
                'is_active'
            ]),

            'teacher' => array_merge($baseHeaders, [
                'nuptk*',              // wajib untuk guru
                'status_kepegawaian*', // tetap, kontrak, honorer
                'bidang_studi*',
                'golongan',            // IV/a, III/d, dll
                'tanggal_masuk*',
                'pendidikan_terakhir*' // S1, S2, D3, dll
            ]),

            'user_only' => array_merge($baseHeaders),
        };
    }

    /**
     * Get example data berdasarkan tipe template (Disesuaikan dengan model)
     */
    protected function getExampleData(string $type): array
    {
        return match ($type) {
            'student' => [
                'Budi Santoso',                    // name
                'budi.siswa@example.com',         // email
                'siswa123',                        // password
                '2024-01-15 08:00:00',             // email_verified_at
                'student',
                '1234567890',                       // nisn
                'Jakarta',                          // tempat_lahir
                '2010-05-15',                        // tanggal_lahir
                'Ahmad Santoso',                     // nama_ayah
                'Siti Aminah',                       // nama_ibu
                'Wiraswasta',                         // pekerjaan_orang_tua
                'Jl. Merdeka No. 123, Jakarta',       // alamat_orang_tua
                '081234567890',                        // no_telp_orang_tua
                '1'                                    // is_active (1 = aktif, 0 = tidak aktif)
            ],

            'teacher' => [
                'Siti Rahayu',                       // name
                'siti.guru@example.com',            // email
                'guru123',                           // password
                '2024-01-15 08:00:00',                // email_verified_at
                'teacher',
                '1234567890123456',                    // nuptk
                'PNS',                                 // status_kepegawaian
                'Matematika',                           // bidang_studi
                'IV/a',                                 // golongan
                '2015-07-01',                            // tanggal_masuk
                'S2'               // pendidikan_terakhir
            ],

            'user_only' => [
                'Admin User',                          // name
                'admin@example.com',                   // email
                'admin123',                             // password
                '2024-01-15 08:00:00',                   // email_verified_at
                'admin'                              // roles (multiple dipisah koma)
            ],
        };
    }

    /**
     * Get instructions berdasarkan tipe template
     */
    protected function getTemplateInstructions(string $type): array
    {
        $commonInstructions = [
            '# 6. Untuk multiple roles, pisahkan dengan koma (contoh: admin,guru)',
            '# 7. Is_active: 1 untuk aktif, 0 untuk tidak aktif',
        ];

        return match ($type) {
            'student' => array_merge($commonInstructions, [
                '# 8. NISN harus unique dan 10 digit angka',
                '# 9. Tanggal lahir minimal 5 tahun yang lalu untuk siswa',
                '# 10. Format tanggal: YYYY-MM-DD',
            ]),

            'teacher' => array_merge($commonInstructions, [
                '# 8. NUPTK harus unique dan 16 digit',
                '# 9. Status kepegawaian: PNS, PPPK, Honorer, Kontrak',
                '# 10. Golongan: I/a, I/b, I/c, I/d, II/a, II/b, II/c, II/d, III/a, III/b, III/c, III/d, IV/a, IV/b, IV/c, IV/d',
                '# 11. Pendidikan terakhir: SMA, D3, S1, S2, S3',
            ]),

            'user_only' => array_merge($commonInstructions, [
                '# 8. Roles yang tersedia: admin, guru, siswa, user',
                '# 9. User tanpa profile hanya bisa login sebagai user biasa',
            ]),
        };
    }

    /**
     * Get filename berdasarkan tipe template
     */
    protected function getTemplateFilename(string $type): string
    {
        $timestamp = now()->format('Ymd_His');

        return match ($type) {
            'student' => "template_import_siswa_{$timestamp}.csv",
            'teacher' => "template_import_guru_{$timestamp}.csv",
            'user_only' => "template_import_user_{$timestamp}.csv",
        };
    }
}
