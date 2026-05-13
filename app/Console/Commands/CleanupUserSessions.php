<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupUserSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'session:cleanup-user {user_id? : ID user yang akan dibersihkan sessionnya}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup sessions untuk user tertentu atau semua expired sessions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');

        if ($userId) {
            // Cleanup session untuk user tertentu
            $deleted = DB::table('sessions')
                ->where('user_id', $userId)
                ->delete();

            $this->info("✅ Berhasil menghapus {$deleted} session untuk user ID: {$userId}");
        } else {
            // Cleanup semua expired sessions
            $expiredTime = now()->subMinutes(config('session.lifetime'))->timestamp;
            
            $deleted = DB::table('sessions')
                ->where('last_activity', '<', $expiredTime)
                ->delete();

            $this->info("✅ Berhasil menghapus {$deleted} expired sessions");
        }

        // Tampilkan statistik session aktif
        $this->showSessionStats();

        return Command::SUCCESS;
    }

    /**
     * Tampilkan statistik session
     */
    protected function showSessionStats()
    {
        $this->newLine();
        $this->info('📊 Statistik Session:');
        
        $totalSessions = DB::table('sessions')->count();
        $authenticatedSessions = DB::table('sessions')->whereNotNull('user_id')->count();
        $guestSessions = DB::table('sessions')->whereNull('user_id')->count();

        $this->table(
            ['Tipe', 'Jumlah'],
            [
                ['Total Sessions', $totalSessions],
                ['Authenticated', $authenticatedSessions],
                ['Guest', $guestSessions],
            ]
        );

        // Tampilkan top 5 users dengan session terbanyak
        $topUsers = DB::table('sessions')
            ->select('user_id', DB::raw('COUNT(*) as session_count'))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderByDesc('session_count')
            ->limit(5)
            ->get();

        if ($topUsers->isNotEmpty()) {
            $this->newLine();
            $this->info('👥 Top 5 Users dengan Session Terbanyak:');
            
            $tableData = $topUsers->map(function ($user) {
                return [
                    'User ID' => $user->user_id,
                    'Sessions' => $user->session_count,
                ];
            })->toArray();

            $this->table(['User ID', 'Sessions'], $tableData);
        }
    }
}
