<div class="header">
    <h1>JURNAL KEGIATAN PEMBELAJARAN</h1>
    <p>Kelas: {{ $record->kelas->nama }} | Mapel: {{ $record->mapel->nama }}</p>
</div>

<table>
    <thead>
        <tr>
            <th>Nama Siswa</th>
            <th>Status (Keterangan)</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($record->absensi as $nama => $status)
            <tr>
                <td>{{ $nama }}</td>
                <td>{{ $status }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
