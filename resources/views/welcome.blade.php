<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

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

        /* Efek blob di belakang burung hantu */
        .blob-bg {
            background-color: #bbf7d0;
            opacity: 0.75;
            /* Light green */
            border-radius: 40% 60% 70% 30% / 40% 50% 60% 80%;
        }
    </style>
</head>

<body class="bg-doodle min-h-screen flex flex-col p-6 lg:p-12">

    <header class="absolute top-0 left-0 w-full h-20 px-6 lg:px-12 flex items-center z-50">
    </header>

    <main class="flex-grow flex items-center justify-center max-w-6xl mx-auto w-full">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center w-full">

            <div class="relative flex justify-center items-center order-first md:order-last">
                <div class="blob-bg absolute w-64 h-64 md:w-80 lg:w-96 -z-10 opacity-70"></div>
                <img src="{{ asset('/img/logo.png') }}" alt="Owl Teacher"
                    class="w-full max-w-[250px] md:max-w-sm lg:max-w-md drop-shadow-2xl">
            </div>

            <div
                class="flex flex-col items-center md:items-start space-y-6 relative pt-4 md:pt-0 order-last md:order-first">

                <div
                    class="hidden lg:block absolute -top-64 -right-64 w-[800px] z-0 pointer-events-none overflow-visible">
                    <img src="{{ asset('img/secondary-logo.png') }}" alt="Flying Books"
                        class="w-full h-auto object-contain">
                </div>

                <h3
                    class="text-xl md:text-2xl font-medium text-slate-700 tracking-tight relative z-10 text-center md:text-left">
                    SMA/SMK Garuda
                </h3>

                <h1
                    class="text-5xl md:text-7xl lg:text-8xl font-bold text-slate-900 leading-none relative z-10 text-center md:text-left">
                    De' Journal
                </h1>

                <div class="space-y-2 relative z-10 text-center md:text-left flex flex-col items-center md:items-start">
                    <p class="text-lg font-semibold text-slate-800 leading-tight">
                        Administrasi Rapi, Mengajar Lebih Bermakna.
                    </p>
                    <p class="text-gray-600 max-w-sm md:max-w-md text-sm md:text-base">
                        Platform digital untuk mencatat perkembangan siswa dan refleksi harian Anda.
                    </p>
                </div>

                <a href="{{ route('filament.admin.pages.dashboard') }}"
                    class="w-full md:w-auto text-center bg-emerald-600 hover:bg-emerald-700 text-white px-10 py-3 rounded-xl font-medium transition-all transform hover:scale-105 shadow-lg cursor-pointer relative z-10">
                    Ayo Mulai!
                </a>
            </div>

        </div>
    </main>

    <footer class="w-full py-4 text-center text-[10px] md:text-xs text-gray-400">
        <p>Copyright © 2026 SMA/SMK Garuda | made with <span class="text-red-500">❤️</span> <a
                href="wa.me/+6281916175060" target="_blank" class="text-emerald-500">adiartawibawa</a> </p>
    </footer>

</body>

</html>
