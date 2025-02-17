<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TandaTangan;

class TandaTanganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $userData = [
            [
            'name' => 'BADRUDIN',
            'jabatan' => 'Operator',
            'nip' => 'Penyedia Jasa',
            'type' => 'pembuat',
            'user_id' => 1
            ],
            [
            'name' => 'BAGUS GEDE ARTA PERDANA, S.KOM. M.KOM',
            'jabatan' => 'Pejabat Pelaksana Teknis Kegiatan',
            'nip' => '19931031 201503 1 002',
            'type' => 'verifikator'
            ],
            [
            'name' => 'SYAIFUL BAHRI, S.Sos',
            'jabatan' => 'Pejabat Pembuat Komitmen',
            'nip' => '19710627 200801 1 009',
            'type' => 'persetujuan'
            ],
            [
            'name' => 'WISMAN SYAH, ST. M.Si',
            'jabatan' => 'Kepala Bidang Pengelolaan Aplikasi dan Persandian',
            'nip' => '19680304 199803 1 008',
            'type' => 'persetujuan'
            ]
        ];

        foreach ($userData as $data) {
            TandaTangan::create($data);
        }
    }
}
