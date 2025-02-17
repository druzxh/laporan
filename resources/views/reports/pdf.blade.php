<!DOCTYPE html>
<html>
<head>
    <title>Laporan {{$bulanNama}} {{$tahun}} - {{$user->name}}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            overflow: visible;
        }

        .decorative-top-left {
            position: absolute;
            top: 40px;
            left: 0;
            width: 40px;
            height: 50%;
            background-color: #2d76b6ff;
        }

        .decorative-top-left2 {
            position: absolute;
            top: 0;
            left: 0;
            width: 400px;
            height: 40px;
            background-color: #2d76b6ff;
        }

        .decorative-bottom-right {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 40px;
            height: 50%;
            background-color: #2d76b6ff;
        }

        .decorative-bottom-right2 {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 400px;
            height: 40px;
            background-color: #2d76b6ff;
        }

        .cover-page {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            position: relative;
            page-break-after: always;
        }

        .cover-text {
            text-align: center;
        }

        .cover-title {
            font-size: 24px;
            font-weight: bold;
        }

        .cover-subtitle {
            font-size: 18px;
        }

        .content-page {
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

        .text-center {
            text-align: center;
        }

        .cover-head-text {
            position: absolute;
            top: 150px;
            left: 110px;
            margin-top: 20px;
        }

        .cover-foot-text {
            position: absolute;
            bottom: 130px;
            right: 110px;
            margin-bottom: 20px;
            text-align: right;
        }

        .title {
            font-size: 1.1em;
            margin: 5px 0;
        }

        .bold{
            font-weight: bold;
        }

        .footer-page {
            page-break-before: always;
        }

        .footer-table {
            width: 100%;
            text-align: center;
            margin-top: 50px;
            border-collapse: collapse;
        }

        .footer-table, .footer-table td {
            border: none;
            padding: 8px;
        }

        .footer-table td {
            padding-top: 50px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="cover-page">
        <div class="decorative-top-left"></div>
        <div class="decorative-top-left2"></div>
        <div class="decorative-bottom-right"></div>
        <div class="decorative-bottom-right2"></div>

        <div class="cover-head-text">
            <p class="title">{{$bulanNama}} {{$tahun}}</p>
            <p class="bold title">Laporan Aktivitas</p>
        </div>
        <div class="cover-foot-text">
            <p class="title">{{$user->name}}</p>
            <p class="bold title">{{$user->jabatan}}</p>
        </div>
    </div>

    <!-- Main -->
    <div class="content-page">
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
                        <img src="{{ public_path('storage/' . $report['gambar'][0]) }}" alt="Lampiran Gambar" style="width: 300px; height: auto;">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer-page">
        <table class="footer-table">
            <tr>
                <td style="width: 50%;">
                    Diverifikasi Oleh,<br>
                    <strong>
                        {{$ttd[1]['jabatan']}}<br>
                    </strong>
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
                    <strong>
                        {{$ttd[0]['jabatan']}}<br>
                    </strong>
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
                    <strong>
                        {{$ttd[2]['jabatan']}}<br>
                    </strong>
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
