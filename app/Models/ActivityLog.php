<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Activity
{
    use HasFactory;

    protected $fillable = [
        'log_name',
        'action',
        'model_type',
        'model_id',
        'user_id',
        'ip_address',
        'user_agent',
        'description',
        'properties',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'event',
        'batch_uuid',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Jika disimpan oleh Spatie Activitylog
            if ($model->causer_id && !$model->user_id) {
                $model->user_id = $model->causer_id;
            }
            if ($model->subject_type && !$model->model_type) {
                $model->model_type = class_basename($model->subject_type);
            }
            if ($model->subject_id && !$model->model_id) {
                $model->model_id = $model->subject_id;
            }
            if ($model->event && !$model->action) {
                $model->action = $model->event;
            }

            // Sebaliknya, jika disimpan manual oleh log bawaan project
            if ($model->user_id && !$model->causer_id) {
                $model->causer_id = $model->user_id;
                $model->causer_type = User::class;
            }
            if ($model->model_type && !$model->subject_type) {
                $model->subject_type = 'App\\Models\\' . $model->model_type;
            }
            if ($model->model_id && !$model->subject_id) {
                $model->subject_id = $model->model_id;
            }
            if ($model->action && !$model->event) {
                $model->event = $model->action;
            }
            
            // Set IP Address dan User Agent otomatis jika kosong
            if (!$model->ip_address) {
                $model->ip_address = request()->ip();
            }
            if (!$model->user_agent) {
                $model->user_agent = request()->userAgent();
            }

            // Pastikan user_id benar-benar ada di tabel users untuk menghindari Foreign Key Violation (misal dari sisa session/cookie lama)
            if ($model->user_id && !\App\Models\User::where('id', $model->user_id)->exists()) {
                $model->user_id = null;
                $model->causer_id = null;
                $model->causer_type = null;
            }
        });

        static::deleting(function ($model) {
            throw new \Exception("Log aktivitas tidak dapat dihapus.");
        });
    }

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
            self::ACTION_DELETE   => 'Hapus Data (Soft Delete)',
            'restore'             => 'Memulihkan Data',
            'force_delete'        => 'Hapus Permanen',
            self::ACTION_IMPORT   => 'Import Excel',
            self::ACTION_EXPORT   => 'Export Data',
            self::ACTION_VALIDATE => 'Validasi',
            self::ACTION_LOGIN    => 'Login',
            'login_failed'        => 'Login Gagal',
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
            self::ACTION_DELETE   => 'warning',
            'restore'             => 'success',
            'force_delete'        => 'danger',
            self::ACTION_IMPORT   => 'primary',
            self::ACTION_EXPORT   => 'gray',
            self::ACTION_VALIDATE => 'success',
            self::ACTION_LOGIN    => 'success',
            'login_failed'        => 'danger',
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
            'restore'             => 'heroicon-o-arrow-path',
            'force_delete'        => 'heroicon-o-x-circle',
            self::ACTION_IMPORT   => 'heroicon-o-arrow-up-tray',
            self::ACTION_EXPORT   => 'heroicon-o-arrow-down-tray',
            self::ACTION_VALIDATE => 'heroicon-o-check-circle',
            self::ACTION_LOGIN    => 'heroicon-o-arrow-right-on-rectangle',
            'login_failed'        => 'heroicon-o-exclamation-triangle',
            self::ACTION_LOGOUT   => 'heroicon-o-arrow-left-on-rectangle',
            self::ACTION_DB_CLEAN => 'heroicon-o-circle-stack',
            default               => 'heroicon-o-information-circle',
        };
    }
}
