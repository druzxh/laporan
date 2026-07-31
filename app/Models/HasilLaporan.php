<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HasilLaporan extends Model
{
    protected $table = 'hasil_laporans';

    protected $fillable = [
        'user_id',
        'hari',
        'tanggal',
        'bulan',
        'tahun',
        'aktifitas',
        'lampiran',
        'diff_text',
    ];

    protected $casts = [
        'lampiran' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
