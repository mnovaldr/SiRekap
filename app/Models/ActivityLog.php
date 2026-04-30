<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'action',
        'model_type',
        'model_id',
        'user_id',
        'ip_address',
        'user_agent',
        'description',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    // ── Action constants ──────────────────────────────────────────────────────
    const ACTION_CREATE   = 'create';
    const ACTION_UPDATE   = 'update';
    const ACTION_DELETE   = 'delete';
    const ACTION_IMPORT   = 'import';
    const ACTION_EXPORT   = 'export';
    const ACTION_VALIDATE = 'validate';
    const ACTION_LOGIN    = 'login';
    const ACTION_LOGOUT   = 'logout';
    const ACTION_DB_CLEAN = 'db_clean';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Static helper ─────────────────────────────────────────────────────────
    public static function log(
        string  $action,
        ?Model  $model       = null,
        ?string $description = null,
        ?array  $properties  = null
    ): self {
        return self::create([
            'action'      => $action,
            'model_type'  => $model ? class_basename($model) : null,
            'model_id'    => $model?->getKey(),
            'user_id'     => auth()->id(),
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
            'description' => $description,
            'properties'  => $properties,
        ]);
    }

    // Alias lama agar tidak break kode yang sudah ada
    public static function logActivity(
        string  $action,
        ?Model  $model       = null,
        ?string $description = null,
        ?array  $properties  = null
    ): self {
        return self::log($action, $model, $description, $properties);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_CREATE   => 'Tambah Data',
            self::ACTION_UPDATE   => 'Edit Data',
            self::ACTION_DELETE   => 'Hapus Data',
            self::ACTION_IMPORT   => 'Import Excel',
            self::ACTION_EXPORT   => 'Export Data',
            self::ACTION_VALIDATE => 'Validasi',
            self::ACTION_LOGIN    => 'Login',
            self::ACTION_LOGOUT   => 'Logout',
            self::ACTION_DB_CLEAN => 'Bersihkan DB',
            default               => ucfirst($this->action),
        };
    }

    public function getActionColorAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_CREATE   => 'success',
            self::ACTION_UPDATE   => 'info',
            self::ACTION_DELETE   => 'danger',
            self::ACTION_IMPORT   => 'primary',
            self::ACTION_EXPORT   => 'gray',
            self::ACTION_VALIDATE => 'success',
            self::ACTION_LOGIN    => 'gray',
            self::ACTION_LOGOUT   => 'gray',
            self::ACTION_DB_CLEAN => 'warning',
            default               => 'gray',
        };
    }

    public function getActionIconAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_CREATE   => 'heroicon-o-plus-circle',
            self::ACTION_UPDATE   => 'heroicon-o-pencil-square',
            self::ACTION_DELETE   => 'heroicon-o-trash',
            self::ACTION_IMPORT   => 'heroicon-o-arrow-up-tray',
            self::ACTION_EXPORT   => 'heroicon-o-arrow-down-tray',
            self::ACTION_VALIDATE => 'heroicon-o-check-circle',
            self::ACTION_LOGIN    => 'heroicon-o-arrow-right-on-rectangle',
            self::ACTION_LOGOUT   => 'heroicon-o-arrow-left-on-rectangle',
            self::ACTION_DB_CLEAN => 'heroicon-o-circle-stack',
            default               => 'heroicon-o-information-circle',
        };
    }
}
