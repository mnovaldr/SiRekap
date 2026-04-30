<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterKategori extends Model
{
    protected $table = 'master_kategori';

    protected $fillable = [
        'type',
        'raw_value',
        'standard_value',
        'frequency',
    ];

    protected $casts = [
        'frequency' => 'integer',
    ];

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}
