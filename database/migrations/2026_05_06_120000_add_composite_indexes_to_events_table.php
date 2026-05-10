<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan composite indexes untuk mempercepat query dashboard dan analitik.
     *
     * Query paling sering digunakan:
     *   WHERE status_data = 'Valid' AND tanggal BETWEEN ? AND ?
     *   WHERE status_data = 'Valid' AND is_primary_row = true AND tanggal BETWEEN ? AND ?
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // CRITICAL: digunakan di hampir semua query widget (valid() + byYearRange)
            $table->index(['status_data', 'tanggal'], 'events_status_tanggal_idx');

            // CRITICAL: digunakan di StatsOverview, EventsChart, semua chart distribusi
            $table->index(['status_data', 'is_primary_row', 'tanggal'], 'events_status_primary_tanggal_idx');

            // HIGH: digunakan di filter sub_sektor + tanggal
            $table->index(['status_data', 'tanggal', 'sub_sektor'], 'events_status_tanggal_subsektor_idx');

            // MEDIUM: digunakan di RekapBulananWidget dan StatsOverview (groupBy bulan)
            $table->index(['status_data', 'bulan'], 'events_status_bulan_idx');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex('events_status_tanggal_idx');
            $table->dropIndex('events_status_primary_tanggal_idx');
            $table->dropIndex('events_status_tanggal_subsektor_idx');
            $table->dropIndex('events_status_bulan_idx');
        });
    }
};
