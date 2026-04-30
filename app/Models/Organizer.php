<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organizer extends Model
{
    protected $fillable = [
        'nama',
        'bidang',
        'email',
        'no_telepon',
        'total_events',
    ];

    protected $casts = [
        'total_events' => 'integer',
    ];
}
