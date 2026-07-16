<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitToken extends Model
{
    protected $fillable = [
        'label',
        'token',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Mutator untuk mengatasi tipe data boolean pada PostgreSQL ketika prepared statements dinonaktifkan (PgBouncer).
     */
    public function setIsActiveAttribute($value)
    {
        $boolVal = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        $this->attributes['is_active'] = $boolVal ? 'true' : 'false';
    }

    /**
     * Cek apakah token masih aktif dan belum expired.
     */
    public function isValid(): bool
    {
        return $this->is_active && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
