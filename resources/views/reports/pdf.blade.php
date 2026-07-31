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
            height: 95vh;
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
            /* Footer page style */
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
        <table style="table-layout: fixed; width: 100%;">
            <thead>
                <tr>
                    <th class="text-center" style="width: 5%;">No.</th>
                    <th style="width: 13%;">Hari/Tanggal</th>
                    <th style="width: 25%;">Deskripsi</th>
                    <th style="width: 57%;">Lampiran</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $no = 1;
                @endphp
                @foreach ($reports as $report)
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>{{ $report['hari'] }},<br>{{ $report['tanggal'] }}/{{ $report['bulan'] }}/{{ $report['tahun'] }}</td>
                    <td>
                        <div style="text-align: justify; line-height: 1.4;">
                            @foreach ($report['aktifitas'] as $aktifitas)
                                <div style="margin-bottom: 5px;">{!! nl2br(e($aktifitas)) !!}</div>
                            @endforeach
                        </div>
                    </td>
                    <td>
                        @if(!empty($report['diff_text']))
                            <div style="background: #f8f9fa; border: 1px solid #ddd; padding: 5px; font-family: monospace; font-size: 7px; word-wrap: break-word; word-break: break-all; line-height: 1.1;">
                                @foreach(explode("\n", $report['diff_text']) as $line)
                                    @php
                                        $line = str_replace("\r", "", $line);
                                        $color = '#000'; $bg = 'transparent'; $fw = 'normal';
                                        if(str_starts_with($line, '+')) { $color = '#28a745'; $bg = '#e6ffed'; }
                                        elseif(str_starts_with($line, '-')) { $color = '#cb2431'; $bg = '#ffeef0'; }
                                        elseif(str_starts_with($line, '@@') || str_starts_with($line, 'File:')) { $color = '#0366d6'; $fw = 'bold'; }
                                    @endphp
                                    <span style="color: {{ $color }}; background-color: {{ $bg }}; font-weight: {{ $fw }}; white-space: pre-wrap;">{{ $line }}</span><br>
                                @endforeach
                            </div>
                        @elseif(!empty($report['gambar'][0]))
                            @php $imgPath = public_path('storage/' . $report['gambar'][0]); @endphp
                            @if(file_exists($imgPath) && !is_dir($imgPath))
                                <img src="{{ $imgPath }}" style="max-width: 100%; height: auto; border-radius: 5px; display: block; margin: 0 auto;">
                            @else
                                <span>Tidak ada lampiran diff text atau gambar</span>
                            @endif
                        @else
                            <span>Tidak ada lampiran diff text atau gambar</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer-page" style="page-break-inside: avoid; margin-top: 40px;">
        <table class="footer-table">
            <tr>
                <td style="width: 50%;">
                    Diverifikasi Oleh,<br>
                    <strong>
                        {{$verifikatorTtd->jabatan}}<br>
                    </strong>
                    <br><br><br><br><br>
                    <strong>
                        <u>
                            {{$verifikatorTtd->name}}
                        </u>
                    </strong><br>
                    {{'NIP. ' . $verifikatorTtd->nip}}
                </td>

                <td style="width: 50%;">
                    Disusun Oleh,<br>
                    <strong>
                        {{$userTtd->jabatan}}<br>
                    </strong>
                    <br><br><br><br><br>
                    <strong>
                        <u>
                            {{$userTtd->name}}
                        </u>
                    </strong><br>
                    {{$userTtd->nip}}
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    Disetujui Oleh,<br>
                    <strong>
                        {{$persetujuanTtd->jabatan}}<br>
                    </strong>
                    <br><br><br><br><br>
                    <strong>
                        <u>
                            {{$persetujuanTtd->name}}
                        </u>
                    </strong><br>
                    {{'NIP. ' . $persetujuanTtd->nip}}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
