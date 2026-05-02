<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'semester')) {
                $table->string('semester', 20)->nullable()->after('course');
            }

            if (! Schema::hasColumn('students', 'section')) {
                $table->string('section', 10)->nullable()->after('semester');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'section')) {
                $table->dropColumn('section');
            }

            if (Schema::hasColumn('students', 'semester')) {
                $table->dropColumn('semester');
            }
        });
    }
};
