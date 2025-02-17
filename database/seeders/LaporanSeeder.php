<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Laporan;
use Carbon\Carbon;

class LaporanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $laporanData = [
            [
                'aktifitas' => 'Meeting tim proyek',
                'hari' => "Selasa",
                'tanggal' => '15',
                'bulan' => '02',
                'tahun' => '2025',
                'gambar' => 'meeting.jpg',
                'user_id' => 1
            ],
            [
                'aktifitas' => 'Presentasi hasil pekerjaan',
                'hari' => "Senin",
                'tanggal' => '16',
                'bulan' => '02',
                'tahun' => '2025',
                'gambar' => 'presentasi.jpg',
                'user_id' => 1
            ],
            [
                'aktifitas' => 'Evaluasi bulanan',
                'hari' => "Selasa",
                'tanggal' => '20',
                'bulan' => '02',
                'tahun' => '2025',
                'gambar' => 'evaluasi.jpg',
                'user_id' => 1
            ],
        ];

        foreach ($laporanData as $data) {
            Laporan::create($data);
        }
    }
}
