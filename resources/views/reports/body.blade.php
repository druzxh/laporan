<!DOCTYPE html>
<html>
<head>
    <title>Laporan Aktivitas - Badrudin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
        }

        .container {
            padding: 20px;
            background-color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
        }

        img {
            width: 300px;
            height: auto;
            display: block;
            margin: 0 auto;
            object-fit: cover;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .text-center {
            text-align: center;
        }

        .footer-page {
            page-break-before: always;
        }

        .footer-table {
            width: 100%;
            text-align: center;
            margin-top: 50px;
            border-collapse: collapse; /* Pastikan border collapse diatur */
        }

        .footer-table, .footer-table td {
            border: none; /* Hapus semua border */
            padding: 8px;
        }

        .footer-table td {
            padding-top: 50px;
        }
    </style>

</head>
<body>
    <div class="container">
        <table>
            <thead>
                <tr>
                    <th class="text-center">No.</th>
                    <th>Hari/Tanggal</th>
                    <th>Deskripsi</th>
                    <th>Lampiran</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; ?>
                @foreach ($reports as $report)
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>{{ $report['hari'] }}, {{ $report['tanggal'] }}/{{ $report['bulan'] }}/{{ $report['tahun'] }}</td>
                    <td>
                        <ul>
                            @foreach ($report['aktifitas'] as $aktifitas)
                            <li>{{ $aktifitas }}</li>
                            @endforeach
                        </ul>
                    </td>
                    <td>
                        <img src="{{ public_path('storage/' . $report['gambar'][0]) }}" alt="Lampiran Gambar">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer-page">
        <table class="footer-table">
            <tr>
                <td style="width: 50%;">
                    Diverifikasi Oleh,<br>
                    {{$ttd[1]['jabatan']}}<br>
                    <br><br><br><br><br>
                    <strong>
                        <u>
                            {{$ttd[1]['name']}}
                        </u>
                    </strong><br>
                    {{$ttd[1]['nip']}}
                </td>

                <td style="width: 50%;">
                    Disusun Oleh,<br>
                    {{$ttd[0]['jabatan']}}<br>
                    <br><br><br><br><br>
                    <strong>
                        <u>
                            {{$ttd[0]['name']}}
                        </u>
                    </strong><br>
                    {{$ttd[0]['nip']}}
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    Disetujui Oleh,<br>
                    {{$ttd[2]['jabatan']}}<br>
                    <br><br><br><br><br>
                    <strong>
                        <u>
                            {{$ttd[2]['name']}}
                        </u>
                    </strong><br>
                    {{$ttd[2]['nip']}}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
