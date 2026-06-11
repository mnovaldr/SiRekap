<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('log_name')->nullable()->after('id');
            $table->nullableMorphs('subject', 'subject'); // subject_type & subject_id
            $table->nullableMorphs('causer', 'causer');   // causer_type & causer_id
            $table->uuid('batch_uuid')->nullable()->after('properties');
            $table->string('event')->nullable()->after('batch_uuid');
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn(['log_name', 'batch_uuid', 'event']);
            $table->dropMorphs('subject');
            $table->dropMorphs('causer');
        });
    }
};
