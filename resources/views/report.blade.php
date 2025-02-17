<h1>Data Laporan</h1>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Judul</th>
            <th>Deskripsi</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($reports as $report)
            <tr>
                <td>{{ $report->id }}</td>
                <td>{{ $report->aktifitas }}</td>
                <td>{{ $report->user_id }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
