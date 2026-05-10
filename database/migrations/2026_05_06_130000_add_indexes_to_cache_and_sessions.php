<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambahkan indexes untuk mempercepat cache dan session lookups.
     */
    public function up(): void
    {
        // Index untuk cache table
        if (Schema::hasTable('cache')) {
            DB::statement('CREATE INDEX IF NOT EXISTS cache_expiration_index ON cache (expiration)');
        }

        // Index untuk sessions table
        if (Schema::hasTable('sessions')) {
            DB::statement('CREATE INDEX IF NOT EXISTS sessions_user_id_index ON sessions (user_id)');
            DB::statement('CREATE INDEX IF NOT EXISTS sessions_last_activity_index ON sessions (last_activity)');
        }

        // Index untuk activity_logs table (untuk widget ActivityStatsWidget)
        if (Schema::hasTable('activity_logs')) {
            DB::statement('CREATE INDEX IF NOT EXISTS activity_logs_created_at_index ON activity_logs (created_at)');
            DB::statement('CREATE INDEX IF NOT EXISTS activity_logs_action_index ON activity_logs (action)');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cache')) {
            DB::statement('DROP INDEX IF EXISTS cache_expiration_index');
        }

        if (Schema::hasTable('sessions')) {
            DB::statement('DROP INDEX IF EXISTS sessions_user_id_index');
            DB::statement('DROP INDEX IF EXISTS sessions_last_activity_index');
        }

        if (Schema::hasTable('activity_logs')) {
            DB::statement('DROP INDEX IF EXISTS activity_logs_created_at_index');
            DB::statement('DROP INDEX IF EXISTS activity_logs_action_index');
        }
    }
};
