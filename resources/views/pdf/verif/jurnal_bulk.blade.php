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

            .print-footer {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: white;
                border-top: 1px solid #e5e7eb;
                padding: 4px 0;
                font-size: 7px;
                color: #6b7280;
                text-align: center;
            }

            body {
                margin-bottom: 1.2cm;
            }
        }

        tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        tbody tr:hover {
            background-color: #e6f0ff;
        }

        .gallery-img:hover {
            transform: scale(1.1);
            transition: transform 0.2s;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .qr-code-container {
            transition: all 0.2s;
        }

        .qr-code-container:hover {
            transform: scale(1.05);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .page-number:before {
            content: "Halaman " counter(page) " dari " counter(pages);
        }

        body {
            counter-reset: page;
        }

        .page-counter {
            counter-increment: page;
        }
    </style>
    <title>Rekapitulasi Jurnal - {{ $settings->nama_sekolah ?? 'Sekolah' }}</title>
</head>

<body class="bg-white p-5 font-sans text-gray-800 page-counter">
    <!-- Kop Surat yang Lebih Elegan -->
    <div class="border-b-4 border-emerald-900 pb-4 mb-5 flex items-center">
        <!-- Logo -->
        <div class="w-20 flex-shrink-0">
            @if (!empty($settings->logo_sekolah))
                <img src="{{ storage_path('app/public/' . $settings->logo_sekolah) }}" class="h-28 w-auto object-contain"
                    alt="Logo Sekolah">
            @else
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center text-gray-400">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356.257l-2.5 2.5a1 1 0 000 1.414l.707.707a1 1 0 001.414 0l2.5-2.5a.999.999 0 01.257.356L8.05 14.25a1 1 0 001.84 0l3-7a1 1 0 000-.788l-3-7z" />
                    </svg>
                </div>
            @endif
        </div>

        <!-- Informasi Sekolah -->
        <div class="flex-1 text-center px-3">
            <h1 class="text-2xl font-bold uppercase tracking-wide text-emerald-900">{{ $settings->nama_sekolah }}</h1>
            <p class="text-sm italic text-gray-600 mt-1">“{{ $settings->motto }}”</p>
            <div class="text-xs text-gray-700 mt-2 leading-relaxed">
                <p>{{ $settings->alamat }}, Kec. {{ $settings->kecamatan }}, {{ $settings->kab_kota }}</p>
                <p class="italic">
                    Telp: {{ $settings->telepon }} | Email: {{ $settings->email }} | Web: {{ $settings->website }}
                </p>
            </div>
        </div>

        <!-- Info Cetak -->
        <div class="w-20 text-right text-[7px] text-gray-400">
            <div>Dicetak:</div>
            <div class="font-semibold text-gray-600">{{ now()->format('d/m/Y H:i') }}</div>
            <div class="mt-1">Oleh: {{ auth()->user()->name ?? 'Admin' }}</div>
        </div>
    </div>

    <!-- Judul Laporan -->
    <div class="text-center mb-6">
        <h2 class="text-lg font-bold uppercase tracking-wider text-gray-800 flex items-center justify-center gap-2">
            <span class="h-px w-8 bg-gray-300"></span>
            REKAPITULASI JURNAL MENGAJAR
            <span class="h-px w-8 bg-gray-300"></span>
        </h2>
        <div class="inline-block bg-emerald-50 px-4 py-1 rounded-full mt-2">
            <p class="text-[9px] font-medium text-emerald-800">
                <span class="font-semibold">Periode:</span>
                @if ($start_date && $end_date)
                    {{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }} -
                    {{ \Carbon\Carbon::parse($end_date)->format('d/m/Y') }}
                @else
                    Semua Data
                @endif
                <span class="mx-2">|</span>
                <span class="font-semibold">Total:</span> {{ $jurnals->count() }} Jurnal
            </p>
        </div>
    </div>

    <!-- Tabel Rekapitulasi -->
    <div class="overflow-hidden rounded-lg border border-gray-300 shadow-sm">
        <table class="w-full border-collapse text-[9px]">
            <thead>
                <tr
                    class="bg-gradient-to-r from-emerald-900 to-emerald-800 text-white uppercase text-[8px] tracking-wider">
                    <th class="border border-emerald-700 p-2 w-8 text-center">No</th>
                    <th class="border border-emerald-700 p-2 w-20">Tanggal</th>
                    <th class="border border-emerald-700 p-2">Guru & Kelas</th>
                    <th class="border border-emerald-700 p-2">Mata Pelajaran</th>
                    <th class="border border-emerald-700 p-2">Materi</th>
                    <th class="border border-emerald-700 p-2 w-24">Dokumentasi</th>
                    <th class="border border-emerald-700 p-2 w-16 text-center">Verifikasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jurnals as $index => $jurnal)
                    <tr class="hover:bg-emerald-50/70 transition-colors duration-150 align-top">
                        <!-- Nomor -->
                        <td class="border border-gray-300 p-2 text-center font-medium">{{ $index + 1 }}</td>

                        <!-- Tanggal -->
                        <td class="border border-gray-300 p-2">
                            <div class="font-bold text-gray-900">{{ $jurnal->tanggal->format('d/m/Y') }}</div>
                            <div class="text-[7px] text-gray-500">{{ $jurnal->tanggal->translatedFormat('l') }}</div>
                        </td>

                        <!-- Guru & Kelas -->
                        <td class="border border-gray-300 p-2">
                            <div class="font-bold text-emerald-900 text-[9px]">{{ $jurnal->guru->user->name }}</div>
                            <div class="mt-1">
                                <span
                                    class="bg-emerald-100 text-emerald-800 text-[7px] px-1.5 py-0.5 rounded-full font-semibold">
                                    {{ $jurnal->kelas->nama }}
                                </span>
                            </div>
                        </td>

                        <!-- Mata Pelajaran -->
                        <td class="border border-gray-300 p-2">
                            <span class="inline-block bg-gray-100 px-2 py-1 rounded text-[8px] font-medium">
                                {{ $jurnal->mapel->nama }}
                            </span>
                        </td>

                        <!-- Materi -->
                        <td class="border border-gray-300 p-2 max-w-[150px]">
                            <p class="line-clamp-3 text-gray-700" title="{{ $jurnal->materi }}">
                                {{ Str::limit($jurnal->materi, 80) }}
                            </p>
                        </td>

                        <!-- Dokumentasi -->
                        <td class="border border-gray-300 p-2">
                            @if ($jurnal->hasMedia('foto_kegiatan'))
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($jurnal->getMedia('foto_kegiatan')->take(2) as $media)
                                        <img src="{{ $media->getPath() }}"
                                            class="w-10 h-8 object-cover border border-gray-200 rounded shadow-sm gallery-img"
                                            alt="Foto Kegiatan"
                                            onclick="window.open('{{ $media->getUrl() }}', '_blank', 'width=800,height=600')">
                                    @endforeach
                                    @if ($jurnal->getMedia('foto_kegiatan')->count() > 2)
                                        <span class="text-[7px] text-emerald-600 font-medium ml-1">
                                            +{{ $jurnal->getMedia('foto_kegiatan')->count() - 2 }}
                                        </span>
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-400 italic text-[7px]">-</span>
                            @endif
                        </td>

                        <!-- QR Code Verifikasi -->
                        <td class="border border-gray-300 p-1.5 text-center bg-white">
                            <div
                                class="qr-code-container inline-block p-1 border border-gray-200 rounded bg-white shadow-sm">
                                @php
                                    $publicUrl = route('jurnal.public.view', ['jurnal' => $jurnal->id]);
                                    $qrcode = QrCode::size(40)->margin(1)->generate($publicUrl);
                                @endphp
                                {!! $qrcode !!}
                            </div>
                            <p class="text-[5px] mt-0.5 font-bold text-emerald-800 uppercase tracking-tight">SCAN</p>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="border border-gray-300 p-12 text-center">
                            <div class="flex flex-col items-center text-gray-400">
                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                <p class="font-medium">Tidak ada data jurnal</p>
                                <p class="text-[9px]">Belum ada jurnal yang dicatat pada periode ini</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Tanda Tangan -->
    <div class="mt-8 flex justify-end no-break">
        <div class="text-center w-64">
            <p class="text-[9px] text-gray-600">{{ $settings->kab_kota ?? 'Kota' }},
                {{ now()->translatedFormat('d F Y') }}</p>
            <p class="text-[9px] font-medium mt-1">Kepala {{ $settings->nama_sekolah ?? 'Sekolah' }}</p>

            <div class="h-16 flex items-center justify-center my-2">
                @if (!empty($settings->ttd_digital))
                    <img src="{{ storage_path('app/public/' . $settings->ttd_digital) }}"
                        class="h-12 w-auto object-contain" alt="Tanda Tangan">
                @else
                    <div class="border-b-2 border-gray-400 w-40 mt-8"></div>
                @endif
            </div>

            <p class="text-[9px] font-bold uppercase">
                {{ $settings->nama_kepala_sekolah ?? '..........................' }}</p>
            <p class="text-[8px] text-gray-600">NIP. {{ $settings->nip_kepala_sekolah ?? '-' }}</p>
        </div>
    </div>

    <!-- Footer dengan Informasi Verifikasi -->
    <div class="mt-8 pt-2 border-t border-gray-200">
        <div class="flex justify-between items-center text-[7px] text-gray-400">
            <div>
                <span class="font-semibold">{{ env('APP_NAME') }} v1.0</span>
                <span class="mx-2">|</span>
                <span>{{ $settings->nama_sekolah ?? 'Sekolah' }}</span>
            </div>
            <div class="text-center">
                <span class="page-number"></span>
            </div>
            <div>
                {{ now()->format('d/m/Y H:i:s') }}
            </div>
        </div>
        <p class="text-[6px] text-gray-300 text-center mt-1">
            * Dokumen ini sah secara digital. Setiap jurnal memiliki QR Code untuk verifikasi keaslian data.
        </p>
    </div>

    <!-- Script untuk memudahkan print -->
    <script>
        (function() {
            // Hitung total halaman saat print preview (simulasi sederhana)
            window.onbeforeprint = function() {
                console.log('Menyiapkan dokumen untuk dicetak...');
            };

            // Buka gambar saat diklik
            document.querySelectorAll('.gallery-img').forEach(img => {
                img.addEventListener('click', function() {
                    const src = this.src;
                    window.open(src, '_blank', 'width=800,height=600,scrollbars=yes');
                });
            });

            // Shortcut Ctrl+P untuk print
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                    e.preventDefault();
                    window.print();
                }
            });
        })();
    </script>
</body>

</html>
