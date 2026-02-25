<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Laporan Jurnal - {{ $jurnal->materi }}</title>
</head>

<body class="bg-white p-4">
    <div class="header-container text-center border-b-4 border-double border-gray-800 pb-4 mb-8">
        <h2 class="text-2xl font-bold uppercase">Laporan Jurnal Mengajar Mandiri</h2>
        <p class="text-gray-600">Tahun Ajaran: {{ $jurnal->tahunAjaran->nama ?? '-' }}</p>
    </div>

    <div class="grid grid-cols-3 border border-gray-300 mb-6 text-sm">
        <div class="bg-gray-100 p-3 font-bold border-r border-b border-gray-300">Nama Guru</div>
        <div class="col-span-2 p-3 border-b border-gray-300">{{ $jurnal->guru->user->name }}</div>

        <div class="bg-gray-100 p-3 font-bold border-r border-b border-gray-300">Mata Pelajaran</div>
        <div class="col-span-2 p-3 border-b border-gray-300">{{ $jurnal->mapel->nama }}</div>

        <div class="bg-gray-100 p-3 font-bold border-r border-gray-300">Kelas / Tanggal</div>
        <div class="col-span-2 p-3">{{ $jurnal->kelas->nama }} / {{ $jurnal->tanggal->format('d F Y') }}</div>
    </div>

    <div class="space-y-4 text-sm">
        <div class="border border-gray-300 p-4 rounded-lg">
            <span class="font-bold underline block mb-2 text-blue-800">Materi Pembelajaran:</span>
            <p class="leading-relaxed">{{ $jurnal->materi }}</p>
        </div>

        <div class="border border-gray-300 p-4 rounded-lg">
            <span class="font-bold underline block mb-2 text-blue-800">Kegiatan Pembelajaran:</span>
            <p class="leading-relaxed">{{ $jurnal->kegiatan }}</p>
        </div>
    </div>

    @if ($jurnal->hasMedia('foto_kegiatan'))
        <div class="mt-8">
            <h3 class="font-bold border-b border-gray-200 pb-2 mb-4">Dokumentasi Kegiatan:</h3>
            <div class="grid grid-cols-2 gap-4">
                @foreach ($jurnal->getMedia('foto_kegiatan') as $media)
                    <img src="{{ $media->getPath() }}"
                        class="w-full h-64 object-cover rounded-md border border-gray-200">
                @endforeach
            </div>
        </div>
    @endif
</body>

</html>
