<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasUuids;

    protected $fillable = [
        'nama_organisasi',
        'bidang',
        'jenis_kegiatan',
        'sub_sektor',
        'komersial',
        'program_sdgs',
        'judul_kegiatan',
        'tanggal',
        'bulan',
        'lantai',
        'lokasi',
        'jumlah_peserta',
        'is_primary_row',
        'email',
        'no_telepon',
        'pic',
        'status_data',
        'catatan_review',
        'key_full',
        'key_short',
    ];

    protected $casts = [
        'tanggal'        => 'date',
        'jumlah_peserta' => 'integer',
        'is_primary_row' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        
        // Logika pengisian otomatis (bulan, key, duplikat) 
        // sekarang ditangani oleh Database Trigger di Supabase.

        static::saved(function () {
            \Illuminate\Support\Facades\Cache::flush();
        });

        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::flush();
        });
    }

    public static function getBulanIndonesia($date)
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        if (!$date) {
            return null;
        }

        // Jika input adalah angka 1-12 (seperti dari filter)
        if (is_numeric($date) && $date >= 1 && $date <= 12) {
            return $months[(int)$date];
        }

        $timestamp = is_numeric($date) ? $date : strtotime($date);
        
        if (!$timestamp) {
            return null;
        }
        
        return $months[(int) date('n', $timestamp)] ?? null;
    }

    public static function makeKeyFull($event)
    {
        return strtoupper(trim($event->nama_organisasi)) . '||' .
               strtoupper(trim($event->judul_kegiatan)) . '||' .
               date('Y-m-d', strtotime($event->tanggal)) . '||' .
               strtoupper(trim($event->lokasi)) . '||' .
               strtoupper(trim($event->lantai));
    }

    public static function makeKeyShort($event)
    {
        return strtoupper(trim($event->nama_organisasi)) . '||' .
               strtoupper(trim($event->judul_kegiatan)) . '||' .
               date('Y-m-d', strtotime($event->tanggal));
    }

    public function checkDuplicates()
    {
        // Check full duplicate
        $fullDuplicate = self::where('key_full', $this->key_full)
            ->where('id', '!=', $this->id)
            ->exists();

        if ($fullDuplicate) {
            $this->status_data = 'Duplikat';
            $this->catatan_review = 'Duplicate FULL';
            return;
        }

        // Check short duplicate (multi-room)
        $shortDuplicate = self::where('key_short', $this->key_short)
            ->where('id', '!=', $this->id)
            ->exists();

        if ($shortDuplicate) {
            $this->status_data = 'Review';
            $this->catatan_review = 'Duplicate SHORT';
            return;
        }

        // No duplicate
        if ($this->status_data !== 'Valid') {
            $this->status_data = 'Valid';
            $this->catatan_review = null;
        }
    }

    public function scopeValid($query)
    {
        return $query->whereRaw("\"status_data\" = 'Valid'");
    }

    public function scopePrimary($query)
    {
        return $query->whereRaw("\"is_primary_row\" = true");
    }

    public function scopeReview($query)
    {
        return $query->where('status_data', '=', 'Review');
    }

    public function scopeDuplikat($query)
    {
        return $query->where('status_data', '=', 'Duplikat');
    }

    public function scopeByYear($query, $year)
    {
        return $query->when($year, fn($q) => $q->whereYear('tanggal', $year));
    }

    public function scopeByYearRange($query, $start, $end)
    {
        return $query->when($start && $end, function ($q) use ($start, $end) {
            $q->whereBetween('tanggal', ["$start-01-01", "$end-12-31"]);
        });
    }

    public function scopeByMonth($query, $month)
    {
        return $query->when($month, fn($q) => $q->whereMonth('tanggal', $month));
    }

    public function scopeBySubSektor($query, $subSektor)
    {
        return $query->when($subSektor, fn($q) => $q->where('sub_sektor', $subSektor));
    }
}
