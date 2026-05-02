<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coordinator_assignments', function (Blueprint $table) {
            if (! Schema::hasColumn('coordinator_assignments', 'school_year')) {
                $table->string('school_year', 20)->nullable()->after('course');
            }
        });

        Schema::table('student_term_assignments', function (Blueprint $table) {
            if (! Schema::hasColumn('student_term_assignments', 'confirmed_at')) {
                $table->timestamp('confirmed_at')->nullable()->after('required_ojt_hours');
            }

            if (! Schema::hasColumn('student_term_assignments', 'confirmed_by')) {
                $table->unsignedBigInteger('confirmed_by')->nullable()->after('confirmed_at');
                $table->foreign('confirmed_by')
                    ->references('id')
                    ->on('coordinators')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_term_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('student_term_assignments', 'confirmed_by')) {
                $table->dropForeign(['confirmed_by']);
                $table->dropColumn('confirmed_by');
            }

            if (Schema::hasColumn('student_term_assignments', 'confirmed_at')) {
                $table->dropColumn('confirmed_at');
            }
        });

        Schema::table('coordinator_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('coordinator_assignments', 'school_year')) {
                $table->dropColumn('school_year');
            }
        });
    }
};
