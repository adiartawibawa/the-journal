@inject('settings', 'App\Settings\GeneralSettings')

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FAQ | {{ $settings->nama_sekolah }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .bg-doodle {
            position: relative;
            isolation: isolate;
            /* Memastikan konten tetap di atas */
        }

        .bg-doodle::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            /* Di bawah konten */
            background-image: url("{{ asset('img/bg.png') }}");
            background-size: cover;
            background-position: center;
            opacity: 0.25;
            /* opacity 25% */
        }
    </style>
</head>

<body class="bg-doodle min-h-screen flex flex-col font-sans antialiased" x-data="{ active: null }">

    <header class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-emerald-100 shadow-sm">
        <nav class="max-w-7xl mx-auto px-6 lg:px-12 h-20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if ($settings->logo_sekolah)
                    <img src="{{ Storage::url($settings->logo_sekolah) }}" alt="Logo {{ $settings->nama_sekolah }}"
                        class="h-10 w-auto object-contain">
                @endif
                <div class="flex flex-col">
                    <span class="font-bold text-lg leading-none text-slate-800">{{ $settings->nama_singkat }}</span>
                    <span class="text-[10px] text-emerald-600 font-medium uppercase tracking-wider">Pusat Bantuan</span>
                </div>
            </div>

            <a href="/"
                class="group text-sm font-semibold text-slate-600 hover:text-emerald-600 transition-all flex items-center gap-2">
                <span class="transition-transform group-hover:-translate-x-1">&larr;</span>
                <span class="hidden md:inline">Kembali ke Beranda</span>
                <span class="md:hidden">Beranda</span>
            </a>
        </nav>
    </header>

    <main class="flex-grow">
        <section class="bg-emerald-600 py-20 text-white relative overflow-hidden">
            {{-- Elemen Dekoratif --}}
            <div
                class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/4 w-96 h-96 bg-emerald-500 rounded-full blur-3xl opacity-50">
            </div>

            <div class="max-w-4xl mx-auto px-6 text-center relative z-10">
                <h1 class="text-3xl md:text-5xl font-extrabold mb-4 tracking-tight">Ada yang bisa kami bantu?</h1>
                <p class="text-emerald-50 text-lg opacity-90 max-w-2xl mx-auto">
                    Temukan panduan dan jawaban cepat seputar penggunaan platform <span
                        class="font-semibold text-white underline decoration-emerald-400">{{ $settings->nama_singkat ?: config('app.name') }}</span>.
                </p>
            </div>
        </section>

        <div class="max-w-7xl mx-auto px-6 lg:px-12  py-12 mt-8 relative z-20">
            <div class="space-y-4">
                @php
                    $appName = $settings->nama_singkat ?: config('app.name');
                    $faqs = [
                        [
                            'q' => "Bagaimana cara mendapatkan akun {$appName}?",
                            'a' =>
                                'Akun dibuatkan secara terpusat oleh Admin IT atau Bagian Kurikulum. Anda akan menerima kredensial login setelah data Anda diinput ke dalam sistem resmi sekolah.',
                        ],
                        [
                            'q' => 'Saya lupa kata sandi, apa yang harus dilakukan?',
                            'a' =>
                                "Gunakan fitur 'Lupa Password' pada halaman login untuk reset mandiri via email. Jika email belum terdaftar, silakan hubungi tim IT di ruang server untuk reset manual.",
                        ],
                        [
                            'q' => 'Apakah data siswa tahun lalu akan tetap ada?',
                            'a' => "Ya. {$appName} mengadopsi sistem arsip permanen. Data histori nilai, kehadiran, dan catatan siswa tetap tersimpan aman berdasarkan Tahun Ajaran masing-masing.",
                        ],
                        [
                            'q' => 'Bagaimana cara mencetak laporan jurnal harian?',
                            'a' =>
                                "Navigasi ke menu 'Laporan' di dashboard Anda, filter berdasarkan rentang tanggal atau kelas, lalu klik tombol 'Ekspor PDF'. Laporan akan terunduh lengkap dengan stempel digital.",
                        ],
                        [
                            'q' => 'Apakah aplikasi ini bisa diakses di smartphone?',
                            'a' =>
                                'Tentu. Platform ini menggunakan teknologi Progressive Web App (PWA) yang sangat ringan dan responsif untuk semua jenis perangkat mobile.',
                        ],
                    ];
                @endphp

                @foreach ($faqs as $index => $item)
                    <div
                        class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden transition-all duration-300 hover:shadow-md active:scale-[0.99]">
                        <button @click="active = (active === {{ $index }} ? null : {{ $index }})"
                            class="w-full px-6 py-5 text-left flex justify-between items-center group focus:outline-none">
                            <span class="font-bold text-slate-700 group-hover:text-emerald-600 transition-colors">
                                {{ $item['q'] }}
                            </span>
                            <div
                                class="ml-4 flex-shrink-0 w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center group-hover:bg-emerald-50 transition-colors">
                                <svg class="w-4 h-4 text-slate-400 transition-transform duration-300"
                                    :class="active === {{ $index }} ? 'rotate-180 text-emerald-500' : ''"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </button>

                        <div x-show="active === {{ $index }}" x-collapse x-cloak>
                            <div class="px-6 pb-6 text-slate-600 leading-relaxed border-t border-slate-50 pt-5">
                                <div class="flex gap-3">
                                    <div class="w-1.5 h-auto bg-emerald-500 rounded-full opacity-50"></div>
                                    <p>{{ $item['a'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div
                class="mt-12 bg-white/60 backdrop-blur-md rounded-lg p-8 border border-emerald-100 text-center shadow-sm">
                <h3 class="text-xl font-bold text-slate-800 italic">Masih memerlukan bantuan teknis?</h3>
                <p class="text-slate-500 text-sm mb-8 mt-2 max-w-md mx-auto">Tim Helpdesk kami siap membantu Anda setiap
                    hari kerja pukul 08:00 - 15:00 WITA.</p>

                <a href="https://wa.me/+6281916175060" target="_blank" rel="noopener noreferrer"
                    class="inline-flex items-center gap-3 bg-slate-900 text-white px-10 py-4 rounded-full hover:bg-emerald-600 transition-all duration-300 shadow-xl shadow-slate-200 hover:shadow-emerald-200 font-bold tracking-wide">
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                        <path
                            d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.438 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z" />
                    </svg>
                    Hubungi WhatsApp Helpdesk
                </a>
            </div>
        </div>
    </main>

    <footer class="w-full py-8 text-center bg-white border-t border-slate-100">
        <p class="text-xs text-slate-400 font-medium tracking-wide uppercase">
            &copy; {{ date('Y') }} {{ $settings->nama_sekolah }}
            <span class="mx-2 text-slate-200">|</span>
            Platform by <a href="https://wa.me/+6281916175060"
                class="text-emerald-500 hover:text-slate-900 transition-colors">adiartawibawa</a>
        </p>
    </footer>

</body>

</html>
