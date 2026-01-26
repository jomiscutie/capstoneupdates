<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This migration makes time_in nullable to allow afternoon-only time-ins.
     * It must run before the data fix migration (2025_12_29_140136).
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Make time_in nullable to allow afternoon-only time-ins
            $table->time('time_in')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Revert to NOT NULL (though this might cause issues with existing null values)
            $table->time('time_in')->nullable(false)->change();
        });
    }
};

