<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('verification_snapshot', 500)->nullable()->after('late_minutes')->comment('Morning time-in verification photo path');
            $table->string('afternoon_verification_snapshot', 500)->nullable()->after('afternoon_late_minutes')->comment('Afternoon time-in verification photo path');
            $table->string('timeout_verification_snapshot', 500)->nullable()->after('time_out')->comment('Time-out verification photo path');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['verification_snapshot', 'afternoon_verification_snapshot', 'timeout_verification_snapshot']);
        });
    }
};
