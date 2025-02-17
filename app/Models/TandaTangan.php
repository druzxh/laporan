<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TandaTangan extends Model
{
    protected $table = 'tanda_tangans';

    protected $fillable = [
        'jabatan',
        'name',
        'nip',
        'type'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
