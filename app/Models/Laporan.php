<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    protected $table = 'laporans';

    protected $fillable = [
        'aktifitas',
        'hari',
        'tanggal',
        'bulan',
        'gambar',
        'tahun',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
