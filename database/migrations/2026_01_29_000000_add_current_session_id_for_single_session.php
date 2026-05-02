<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Enables single-session: when the same account logs in elsewhere, the previous session is invalidated.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('students', 'current_session_id')) {
            Schema::table('students', function (Blueprint $table) {
                $table->string('current_session_id', 255)->nullable();
            });
        }

        if (! Schema::hasColumn('coordinators', 'current_session_id')) {
            Schema::table('coordinators', function (Blueprint $table) {
                $table->string('current_session_id', 255)->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('students', 'current_session_id')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn('current_session_id');
            });
        }

        if (Schema::hasColumn('coordinators', 'current_session_id')) {
            Schema::table('coordinators', function (Blueprint $table) {
                $table->dropColumn('current_session_id');
            });
        }
    }
};
