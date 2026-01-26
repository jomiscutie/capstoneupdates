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
        Schema::table('attendances', function (Blueprint $table) {
            $table->time('afternoon_time_in')->nullable()->after('time_in');
            $table->boolean('afternoon_is_late')->default(false)->after('afternoon_time_in');
            $table->integer('afternoon_late_minutes')->nullable()->after('afternoon_is_late');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['afternoon_time_in', 'afternoon_is_late', 'afternoon_late_minutes']);
        });
    }
};
