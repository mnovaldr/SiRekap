<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->index('is_primary_row');
            $table->index('jenis_kegiatan');
            $table->index('komersial');
            $table->index('lokasi');
            $table->index('bidang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['is_primary_row']);
            $table->dropIndex(['jenis_kegiatan']);
            $table->dropIndex(['komersial']);
            $table->dropIndex(['lokasi']);
            $table->dropIndex(['bidang']);
        });
    }
};
