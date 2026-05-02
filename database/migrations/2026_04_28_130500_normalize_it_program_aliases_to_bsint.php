<?php

use App\Support\ProgramAlias;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $aliases = [
        'Bachelor of Science in Information Technology',
        'INFORMATION TECHNOLOGY',
        'BSIT',
        'BSINT',
        'BSiNT',
    ];

    public function up(): void
    {
        $target = ProgramAlias::BSINT;

        DB::table('students')
            ->whereIn('course', $this->aliases)
            ->update(['course' => $target]);

        DB::table('students')
            ->whereIn('major', $this->aliases)
            ->update(['major' => $target]);

        DB::table('student_term_assignments')
            ->whereIn('course', $this->aliases)
            ->update(['course' => $target]);

        DB::table('coordinator_assignments')
            ->whereIn('course', $this->aliases)
            ->update(['course' => $target]);

        DB::table('coordinators')
            ->whereIn('major', $this->aliases)
            ->update(['major' => $target]);

        DB::table('coordinators')
            ->whereIn('department', $this->aliases)
            ->update(['department' => $target]);

        DB::table('dynamic_options')
            ->where('type', 'program')
            ->whereIn('value', $this->aliases)
            ->update(['value' => $target]);
    }

    public function down(): void
    {
        DB::table('students')
            ->where('course', ProgramAlias::BSINT)
            ->update(['course' => 'Bachelor of Science in Information Technology']);

        DB::table('students')
            ->where('major', ProgramAlias::BSINT)
            ->update(['major' => 'Bachelor of Science in Information Technology']);

        DB::table('student_term_assignments')
            ->where('course', ProgramAlias::BSINT)
            ->update(['course' => 'Bachelor of Science in Information Technology']);

        DB::table('coordinator_assignments')
            ->where('course', ProgramAlias::BSINT)
            ->update(['course' => 'Bachelor of Science in Information Technology']);

        DB::table('coordinators')
            ->where('major', ProgramAlias::BSINT)
            ->update(['major' => 'Bachelor of Science in Information Technology']);

        DB::table('coordinators')
            ->where('department', ProgramAlias::BSINT)
            ->update(['department' => 'Bachelor of Science in Information Technology']);

        DB::table('dynamic_options')
            ->where('type', 'program')
            ->where('value', ProgramAlias::BSINT)
            ->update(['value' => 'Bachelor of Science in Information Technology']);
    }
};
