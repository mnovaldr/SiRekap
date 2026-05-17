<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Kolom Program SDGS — hanya ada di data tahun 2025+
            // Nullable karena data 2023/2024 tidak memiliki kolom ini
            $table->string('program_sdgs', 100)->nullable()->after('komersial');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('program_sdgs');
        });
    }
};
