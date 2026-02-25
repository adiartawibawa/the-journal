<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Rekapitulasi Jurnal</title>
</head>

<body class="bg-white p-2">
    <div class="text-center border-b-2 border-black pb-4 mb-6">
        <h2 class="text-xl font-bold uppercase">Rekapitulasi Jurnal Mengajar & Dokumentasi</h2>
        <p class="text-sm italic text-gray-600">Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table class="w-full border-collapse border border-gray-400 text-[10px]">
        <thead>
            <tr class="bg-gray-200 uppercase">
                <th class="border border-gray-400 p-2 w-8">No</th>
                <th class="border border-gray-400 p-2 w-24">Tanggal</th>
                <th class="border border-gray-400 p-2">Guru & Kelas</th>
                <th class="border border-gray-400 p-2">Materi & Kegiatan</th>
                <th class="border border-gray-400 p-2 w-1/3">Dokumentasi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($jurnals as $index => $jurnal)
                <tr class="align-top">
                    <td class="border border-gray-400 p-2 text-center">{{ $index + 1 }}</td>
                    <td class="border border-gray-400 p-2 text-center">{{ $jurnal->tanggal->format('d/m/Y') }}</td>
                    <td class="border border-gray-400 p-2">
                        <div class="font-bold text-blue-900">{{ $jurnal->guru->user->name }}</div>
                        <div class="text-gray-600">{{ $jurnal->kelas->nama }}</div>
                        <div class="mt-1 italic">{{ $jurnal->mapel->nama }}</div>
                    </td>
                    <td class="border border-gray-400 p-2">
                        <div class="mb-1 uppercase font-semibold text-[9px]">Materi:</div>
                        <p class="mb-2">{{ $jurnal->materi }}</p>
                        <div class="mb-1 uppercase font-semibold text-[9px]">Kegiatan:</div>
                        <p>{{ $jurnal->kegiatan }}</p>
                    </td>
                    <td class="border border-gray-400 p-2">
                        <div class="flex flex-wrap gap-1">
                            @foreach ($jurnal->getMedia('foto_kegiatan') as $media)
                                <img src="{{ $media->getPath() }}"
                                    class="w-16 h-12 object-cover border border-gray-300 rounded shadow-sm">
                            @endforeach
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
