<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterLokasi extends Model
{
    protected $table = 'master_lokasi';

    protected $fillable = [
        'raw_value',
        'standard_value',
        'frequency',
    ];

    protected $casts = [
        'frequency' => 'integer',
    ];
}
