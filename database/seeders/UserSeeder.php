<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userData = [
            [
            'name' => 'Badrudin',
            'email' => 'badrudin.on@gmail.com',
            'password' => bcrypt('1234'),
            'jabatan' => 'Junior Programmer 3'
            ]
        ];

        foreach ($userData as $data) {
            User::create($data);
        }
    }
}
