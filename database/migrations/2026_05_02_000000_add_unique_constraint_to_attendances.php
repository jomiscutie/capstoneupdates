<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add unique constraint to prevent duplicate attendance records.
     * Only one valid (non-invalidated) attendance record per student per day.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Unique constraint: one valid attendance per student per date
            // This prevents race conditions when multiple kiosks try to record simultaneously
            $table->unique(['student_id', 'date'], 'attendances_student_date_unique');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendances_student_date_unique');
        });
    }
};
