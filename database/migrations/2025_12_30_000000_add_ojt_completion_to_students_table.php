<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('students', 'required_ojt_hours')) {
            Schema::table('students', function (Blueprint $table) {
                $table->decimal('required_ojt_hours', 8, 2)->default(120)->after('face_encoding');
            });
        }

        if (! Schema::hasColumn('students', 'ojt_completion_confirmed_at')) {
            Schema::table('students', function (Blueprint $table) {
                $table->timestamp('ojt_completion_confirmed_at')->nullable()->after('required_ojt_hours');
            });
        }

        if (! Schema::hasColumn('students', 'ojt_confirmed_by')) {
            Schema::table('students', function (Blueprint $table) {
                $table->foreignId('ojt_confirmed_by')->nullable()->constrained('coordinators')->nullOnDelete()->after('ojt_completion_confirmed_at');
            });
        }
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'ojt_confirmed_by')) {
                $table->dropForeign(['ojt_confirmed_by']);
            }
        });

        Schema::table('students', function (Blueprint $table) {
            $columns = array_values(array_filter([
                Schema::hasColumn('students', 'required_ojt_hours') ? 'required_ojt_hours' : null,
                Schema::hasColumn('students', 'ojt_completion_confirmed_at') ? 'ojt_completion_confirmed_at' : null,
                Schema::hasColumn('students', 'ojt_confirmed_by') ? 'ojt_confirmed_by' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
