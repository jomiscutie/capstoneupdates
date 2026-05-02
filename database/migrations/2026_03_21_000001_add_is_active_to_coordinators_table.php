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
        Schema::table('coordinators', function (Blueprint $table) {
            if (! Schema::hasColumn('coordinators', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('current_session_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coordinators', function (Blueprint $table) {
            if (Schema::hasColumn('coordinators', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
