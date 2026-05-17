<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Memperluas kolom program_sdgs dari varchar(100) ke varchar(255)
     * agar mendukung multi-value SDGS dari data 2025
     * (contoh: "SDGS 4; SDGS 5; SDGS 18" = 22+ karakter per entry).
     *
     * Juga memastikan kolom kontak (email, no_telepon, pic) tetap nullable
     * agar data 2023 yang tidak memiliki kolom ini dapat diimport tanpa error.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Perluas program_sdgs untuk mendukung multi-value SDGS (2025+)
            $table->string('program_sdgs', 255)->nullable()->change();

            // Pastikan kolom kontak tetap nullable (tidak wajib untuk data 2023)
            $table->string('email', 255)->nullable()->change();
            $table->string('no_telepon', 50)->nullable()->change();
            $table->string('pic', 150)->nullable()->change();

            // Pastikan sub_sektor tetap nullable
            $table->string('sub_sektor', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('program_sdgs', 100)->nullable()->change();
        });
    }
};
