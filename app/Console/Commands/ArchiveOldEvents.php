<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Cache;

class ArchiveOldEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:archive-old-events {--years=5 : The retention period in years}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive events that are older than the specified retention period (default: 5 years)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $years = (int) $this->option('years');
        $cutoffDate = now()->subYears($years)->toDateString();

        // Cari data aktif (bukan Dihapus, bukan sudah Diarsipkan) yang lebih tua dari cutoffDate
        $query = Event::where('tanggal', '<', $cutoffDate)
            ->whereNotIn('status_data', ['Dihapus', 'Diarsipkan']);

        $count = $query->count();

        if ($count === 0) {
            $this->info("Tidak ada data event yang perlu diarsipkan (retensi > {$years} tahun).");
            return 0;
        }

        // Jalankan update status massal
        $query->update([
            'status_data' => 'Diarsipkan',
        ]);

        // Catat ke Activity Log
        ActivityLog::create([
            'action'      => 'db_clean',
            'model_type'  => null,
            'model_id'    => null,
            'user_id'     => null, // Berjalan oleh sistem/cron
            'ip_address'  => '127.0.0.1',
            'user_agent'  => 'Console Command Scheduler',
            'description' => "System mengarsipkan secara otomatis sejumlah {$count} data event lama (retensi > {$years} tahun).",
            'properties'  => [
                'retention_years' => $years,
                'archived_count'  => $count,
                'cutoff_date'     => $cutoffDate,
            ],
        ]);

        Cache::flush();

        $this->info("Selesai: {$count} data event berhasil diarsipkan (retensi > {$years} tahun).");
        return 0;
    }
}
