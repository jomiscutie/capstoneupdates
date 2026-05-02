<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('students', 'verification_status')) {
            Schema::table('students', function (Blueprint $table) {
                $table->string('verification_status', 20)->default('pending')->after('face_encoding');
            });
        }

        if (! Schema::hasColumn('students', 'verified_at')) {
            Schema::table('students', function (Blueprint $table) {
                $table->timestamp('verified_at')->nullable()->after('verification_status');
            });
        }

        if (! Schema::hasColumn('students', 'verified_by')) {
            Schema::table('students', function (Blueprint $table) {
                $table->foreignId('verified_by')->nullable()->constrained('coordinators')->nullOnDelete()->after('verified_at');
            });
        }

        if (! Schema::hasColumn('students', 'rejected_at')) {
            Schema::table('students', function (Blueprint $table) {
                $table->timestamp('rejected_at')->nullable()->after('verified_by');
            });
        }

        if (! Schema::hasColumn('students', 'rejected_by')) {
            Schema::table('students', function (Blueprint $table) {
                $table->foreignId('rejected_by')->nullable()->constrained('coordinators')->nullOnDelete()->after('rejected_at');
            });
        }

        // Existing students are treated as already verified so current behavior is preserved
        if (Schema::hasColumn('students', 'verification_status') && Schema::hasColumn('students', 'verified_at')) {
            DB::table('students')
                ->whereNull('verification_status')
                ->update([
                    'verification_status' => 'verified',
                    'verified_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'verified_by')) {
                $table->dropForeign(['verified_by']);
            }
            if (Schema::hasColumn('students', 'rejected_by')) {
                $table->dropForeign(['rejected_by']);
            }
        });

        Schema::table('students', function (Blueprint $table) {
            $columns = array_values(array_filter([
                Schema::hasColumn('students', 'verification_status') ? 'verification_status' : null,
                Schema::hasColumn('students', 'verified_at') ? 'verified_at' : null,
                Schema::hasColumn('students', 'verified_by') ? 'verified_by' : null,
                Schema::hasColumn('students', 'rejected_at') ? 'rejected_at' : null,
                Schema::hasColumn('students', 'rejected_by') ? 'rejected_by' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
