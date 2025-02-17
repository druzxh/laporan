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
            'jabatan' => 'Disusun Oleh,',
            'nip' => 'Penyedia Jasa',
            'type' => 'pembuat'
            ],
            [
            'name' => 'BAGUS GEDE ARTA PERDANA, S.KOM. M.KOM',
            'jabatan' => 'Pejabat Pelaksana Teknis Kegiatan,',
            'nip' => 'NIP. 19931031 201503 1 002',
            'type' => 'verifikator'
            ],
            [
            'name' => 'SYAIFUL BAHRI, S.Sos',
            'jabatan' => 'Pejabat Pembuat Komitmen,',
            'nip' => 'NIP. 19710627 200801 1 009',
            'type' => 'persetujuan'
            ]
        ];

        foreach ($userData as $data) {
            TandaTangan::create($data);
        }
    }
}
