<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .page-break {
                page-break-after: always;
            }

            .no-break {
                page-break-inside: avoid;
            }
        }

        body {
            counter-reset: page pages;
        }

        .page-counter {
            counter-increment: page;
        }
    </style>
    <title>Laporan Jurnal - {{ $jurnal->materi }}</title>
</head>

<body class="bg-white p-6 font-sans page-counter">
    <!-- Kop Surat -->
    <div class="flex items-start border-b-4 border-emerald-900 pb-4 mb-6">
        @if ($settings->logo_sekolah)
            <div class="flex-shrink-0">
                <img src="{{ Storage::url($settings->logo_sekolah) }}" class="w-28 h-28 object-contain">
            </div>
        @endif
        <div class="flex-1 text-center px-4">
            <h1 class="text-2xl font-bold uppercase tracking-wide text-emerald-900">{{ $settings->nama_sekolah }}</h1>
            <p class="text-sm italic text-gray-600 mt-1">“{{ $settings->motto }}”</p>
            <div class="text-xs text-gray-700 mt-2 leading-relaxed">
                <p>{{ $settings->alamat }}, Kec. {{ $settings->kecamatan }}, {{ $settings->kab_kota }}</p>
                <p class="italic">
                    Telp: {{ $settings->telepon }} | Email: {{ $settings->email }} | Web: {{ $settings->website }}
                </p>
            </div>
        </div>
        <div class="flex-shrink-0 w-24"></div>
    </div>

    <!-- Judul Laporan -->
    <div class="text-center mb-4">
        <h2
            class="text-2xl font-bold uppercase text-gray-800 border-b-2 border-double border-gray-400 pb-2 inline-block px-6">
            Laporan Jurnal Mengajar
        </h2>
        <p class="text-gray-600 mt-2">Tahun Ajaran: <span
                class="font-semibold">{{ $jurnal->tahunAjaran->nama ?? '-' }}</span></p>
    </div>

    <!-- Informasi Utama dengan desain card -->
    <div class="grid grid-cols-3 border border-gray-300 rounded-lg overflow-hidden shadow-sm mb-4 text-sm">
        <div class="bg-gray-100 p-2 font-semibold text-gray-700 border-r border-b border-gray-300">Nama Guru</div>
        <div class="col-span-2 p-2 border-b border-gray-300 bg-white">{{ $jurnal->guru->user->name }}</div>

        <div class="bg-gray-100 p-2 font-semibold text-gray-700 border-r border-b border-gray-300">Mata Pelajaran</div>
        <div class="col-span-2 p-2 border-b border-gray-300 bg-white">{{ $jurnal->mapel->nama }}</div>

        <div class="bg-gray-100 p-2 font-semibold text-gray-700 border-r border-gray-300">Kelas / Tanggal</div>
        <div class="col-span-2 p-2 bg-white">
            <span class="font-medium">{{ $jurnal->kelas->nama }}</span> /
            <span class="text-gray-600">{{ $jurnal->tanggal->format('d F Y') }}</span>
        </div>
    </div>

    <!-- Materi dan Kegiatan -->
    <div class="space-y-6 mb-4">
        <div class="border-l-4 border-emerald-600 bg-emerald-50 p-5 rounded-r-lg shadow-sm no-break">
            <h3 class="font-bold text-emerald-800 mb-3 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z" />
                </svg>
                Materi Pembelajaran
            </h3>
            <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $jurnal->materi }}</p>
        </div>

        <div class="border-l-4 border-green-600 bg-green-50 p-5 rounded-r-lg shadow-sm no-break">
            <h3 class="font-bold text-green-800 mb-3 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356.257l-2.5 2.5a1 1 0 000 1.414l.707.707a1 1 0 001.414 0l2.5-2.5a.999.999 0 01.257.356L8.05 14.25a1 1 0 001.84 0l3-7a1 1 0 000-.788l-3-7z" />
                    <path d="M7 8a1 1 0 100-2 1 1 0 000 2z" />
                </svg>
                Kegiatan Pembelajaran
            </h3>
            <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $jurnal->kegiatan }}</p>
        </div>
    </div>

    <!-- Dokumentasi Kegiatan -->
    @if ($jurnal->hasMedia('foto_kegiatan'))
        <div class="mt-8 pb-8 no-break">
            <h3 class="font-bold text-lg border-b-2 border-gray-300 pb-2 mb-5 text-gray-700 flex items-center">
                <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Dokumentasi Kegiatan
            </h3>
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($jurnal->getMedia('foto_kegiatan') as $index => $media)
                    <div class="border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                        <img src="{{ $media->getUrl() }}"
                            class="w-full h-48 object-cover hover:scale-105 transition duration-300"
                            alt="Dokumentasi Kegiatan">
                    </div>
                    <!-- Tambahkan page break jika diperlukan (setiap 6 foto) -->
                    @if (($index + 1) % 6 == 0 && !$loop->last)
            </div>
            <div class="page-break"></div>
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
    @endif
    @endforeach
    </div>
    </div>
    @endif

    <!-- Tanda Tangan -->
    <div class="mt-16 grid grid-cols-2 gap-8 text-sm no-break">
        <!-- Kolom Kiri - Kepala Sekolah -->
        <div class="text-center">
            <p class="font-medium text-gray-700">Mengetahui,</p>
            <p class="text-gray-600 mb-6">Kepala {{ $settings->nama_sekolah }}</p>

            <!-- Area Tanda Tangan -->
            <div class="h-20 flex items-end justify-center mb-2">
                @if ($settings->ttd_digital)
                    <img src="{{ storage_path('app/public/' . $settings->ttd_digital) }}"
                        class="max-h-16 object-contain" alt="Tanda Tangan Kepala Sekolah">
                @else
                    <div class="border-b-2 border-gray-400 w-40"></div>
                @endif
            </div>

            <!-- Nama dan NIP -->
            <div>
                <p class="font-bold text-gray-800 uppercase">{{ $settings->nama_kepala_sekolah }}</p>
                <p class="text-gray-600 text-xs">NIP. {{ $settings->nip_kepala_sekolah ?? '-' }}</p>
            </div>
        </div>

        <!-- Kolom Kanan - Guru -->
        <div class="text-center">
            <p class="text-gray-600">{{ $settings->kab_kota }}, {{ now()->translatedFormat('d F Y') }}</p>
            <p class="font-medium text-gray-700 mb-6">Guru Mata Pelajaran,</p>

            <!-- Area Tanda Tangan -->
            <div class="h-20 flex items-end justify-center mb-2">
                {{-- <div class="border-b-2 border-gray-400 w-40"></div> --}}
            </div>

            <!-- Nama dan NUPTK -->
            <div>
                <p class="font-bold text-gray-800 uppercase">{{ $jurnal->guru->user->name }}</p>
                <p class="text-gray-600 text-xs">NUPTK. {{ $jurnal->guru->nuptk }}</p>
            </div>
        </div>
    </div>

    <!-- QR Code di bagian bawah - DIPERKECIL -->
    <div class="mt-10 flex flex-col items-center justify-center no-break">
        <!-- Garis pembatas tipis -->
        <div class="w-32 border-t border-gray-300 mb-4"></div>

        <!-- QR Code lebih kecil -->
        <div class="p-1.5 border border-gray-200 rounded bg-white inline-block">
            @php
                $verificationUrl = route('jurnal.public.view', ['jurnal' => $jurnal->id]);
                $qrcode = QrCode::size(60)->generate($verificationUrl);
            @endphp
            {!! $qrcode !!}
        </div>

        <!-- Teks kecil -->
        <div class="text-center mt-1">
            <p class="text-[8px] font-medium text-gray-500 uppercase tracking-wider">VERIFIKASI ONLINE</p>
            <p class="text-[7px] text-gray-400 mt-0.5">Scan untuk memverifikasi keaslian</p>
        </div>

        <!-- URL kecil (opsional) -->
        <p class="text-[6px] text-gray-300 mt-1 max-w-full truncate">{{ $verificationUrl }}</p>
    </div>

    <!-- Footer dengan JavaScript untuk menghitung total halaman -->
    <div class="fixed bottom-0 left-0 right-0 text-center text-[8px] text-gray-400 border-t border-gray-200 pt-1 pb-1 bg-white print:bg-white"
        id="print-footer">
        <div class="flex justify-between items-center px-4">
            <div class="w-1/3 text-left">
                <span>{{ $settings->nama_sekolah }}</span>
            </div>
            <div class="w-1/3 text-center">
                <span id="page-number">Halaman <span id="current-page">1</span> dari <span
                        id="total-pages">1</span></span>
            </div>
            <div class="w-1/3 text-right">
                <span>{{ now()->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>

    <script>
        (function() {
            // Hitung total halaman saat print preview
            function updatePageNumbers() {
                // Ini adalah simulasi sederhana
                // Dalam implementasi nyata, Anda perlu menghitung berdasarkan tinggi konten

                // Mendapatkan tinggi konten dan tinggi halaman
                const body = document.body;
                const html = document.documentElement;

                const height = Math.max(
                    body.scrollHeight, body.offsetHeight,
                    html.clientHeight, html.scrollHeight, html.offsetHeight
                );

                // Asumsikan tinggi halaman A4 dengan margin = 1123px (sekitar 29.7cm)
                const pageHeight = 1123;
                const totalPages = Math.ceil(height / pageHeight);

                // Update total pages
                document.getElementById('total-pages').textContent = totalPages;

                // Untuk current page, perlu logika lebih kompleks
                // Ini hanya contoh sederhana
            }

            window.onbeforeprint = updatePageNumbers;
            window.onload = updatePageNumbers;
        })();
    </script>

    <style>
        /* Styling untuk footer di setiap halaman saat print */
        @media print {
            .fixed {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
            }

            body {
                margin-bottom: 1.5cm;
                /* Memberi ruang untuk footer */
            }

            /* Memastikan footer tidak terpotong */
            .page-counter {
                position: relative;
            }

            /* Sembunyikan footer di halaman terakhir jika tidak diinginkan */
            /* .page-counter:last-child .fixed { display: none; } */
        }
    </style>
</body>

</html>
