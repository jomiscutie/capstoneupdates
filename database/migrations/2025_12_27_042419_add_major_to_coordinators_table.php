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
            if (!Schema::hasColumn('coordinators', 'major')) {
                $table->string('major')->nullable()->after('college');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coordinators', function (Blueprint $table) {
            if (Schema::hasColumn('coordinators', 'major')) {
                $table->dropColumn('major');
            }
        });
    }
};
