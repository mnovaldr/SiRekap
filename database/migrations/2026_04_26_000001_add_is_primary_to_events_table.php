<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // true = baris utama (peserta dihitung), false = baris split (peserta = 0)
            $table->boolean('is_primary_row')->default(true)->after('jumlah_peserta');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('is_primary_row');
        });
    }
};
