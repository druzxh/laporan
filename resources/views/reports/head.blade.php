<!DOCTYPE html>
<html>
<head>
    <title>Laporan {{$bulanNama}} {{$tahun}} - {{$user->name}}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            height: 100vh;
            position: relative;
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

        .header-text {
            position: absolute;
            top: 150px;
            left: 110px;
            margin-top: 20px;
        }

        .footer-text {
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
    </style>
</head>
<body>
    <div class="decorative-top-left"></div>
    <div class="decorative-top-left2"></div>
    <div class="decorative-bottom-right"></div>
    <div class="decorative-bottom-right2"></div>
    
    <div class="header-text">
        <p class="title">{{$bulanNama}} {{$tahun}}</p>
        <p class="bold title">LAPORAN AKTIVITAS</p>
    </div>

    <div class="footer-text">
        <p class="bold title">{{$user->name}}</p>
        <p class="bold title">{{$user->jabatan}}</p>
    </div>
</body>
</html>
