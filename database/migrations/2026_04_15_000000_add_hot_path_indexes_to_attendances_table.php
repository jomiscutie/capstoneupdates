<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Fast lookup for student daily attendance reads/writes in time-in/out flows.
            $table->index(['student_id', 'date', 'is_invalid'], 'attendances_student_date_valid_idx');

            // Fast dashboard/report filters by date while excluding invalidated rows.
            $table->index(['date', 'is_invalid'], 'attendances_date_valid_idx');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('attendances_student_date_valid_idx');
            $table->dropIndex('attendances_date_valid_idx');
        });
    }
};
