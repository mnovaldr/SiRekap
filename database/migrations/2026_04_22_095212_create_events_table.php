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
        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_organisasi');
            $table->string('bidang', 100);
            $table->string('jenis_kegiatan', 100);
            $table->string('sub_sektor', 100)->nullable();
            $table->string('komersial', 50)->default('Non Komersial');
            $table->string('judul_kegiatan');
            $table->date('tanggal');
            $table->string('bulan', 50)->nullable();
            $table->string('lantai', 10);
            $table->string('lokasi', 150);
            $table->integer('jumlah_peserta')->default(0);
            $table->string('email')->nullable();
            $table->string('no_telepon', 50)->nullable();
            $table->string('pic', 150)->nullable();
            $table->string('status_data', 50)->default('Valid');
            $table->text('catatan_review')->nullable();
            $table->text('key_full')->nullable();
            $table->text('key_short')->nullable();
            $table->timestamps();
            
            $table->index('tanggal');
            $table->index('bulan');
            $table->index('status_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
