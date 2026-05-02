<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const TARGET = 'Bachelor of Science in Information Technology';

    private array $aliases = [
        'BSiNT',
        'BSINT',
        'BSIT',
        'INFORMATION TECHNOLOGY',
        'Bachelor of Science in Information Technology',
    ];

    public function up(): void
    {
        DB::table('students')
            ->whereIn('course', $this->aliases)
            ->update(['course' => self::TARGET]);

        DB::table('students')
            ->whereIn('major', $this->aliases)
            ->update(['major' => self::TARGET]);

        DB::table('student_term_assignments')
            ->whereIn('course', $this->aliases)
            ->update(['course' => self::TARGET]);

        DB::table('coordinator_assignments')
            ->whereIn('course', $this->aliases)
            ->update(['course' => self::TARGET]);

        DB::table('coordinators')
            ->whereIn('major', $this->aliases)
            ->update(['major' => self::TARGET]);

        DB::table('coordinators')
            ->whereIn('department', $this->aliases)
            ->update(['department' => self::TARGET]);

        $programRows = DB::table('dynamic_options')
            ->where('type', 'program')
            ->whereIn('value', $this->aliases)
            ->orderBy('id')
            ->get(['id', 'value', 'is_active']);

        if ($programRows->isNotEmpty()) {
            $targetRow = $programRows->firstWhere('value', self::TARGET);

            if (! $targetRow) {
                DB::table('dynamic_options')->insert([
                    'type' => 'program',
                    'value' => self::TARGET,
                    'is_active' => (bool) ($programRows->first()->is_active ?? true),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('dynamic_options')
                    ->where('id', $targetRow->id)
                    ->update(['is_active' => true, 'updated_at' => now()]);
            }

            DB::table('dynamic_options')
                ->where('type', 'program')
                ->whereIn('value', $this->aliases)
                ->where('value', '!=', self::TARGET)
                ->delete();
        }
    }

    public function down(): void
    {
        DB::table('students')
            ->where('course', self::TARGET)
            ->update(['course' => 'BSiNT']);

        DB::table('students')
            ->where('major', self::TARGET)
            ->update(['major' => 'BSiNT']);

        DB::table('student_term_assignments')
            ->where('course', self::TARGET)
            ->update(['course' => 'BSiNT']);

        DB::table('coordinator_assignments')
            ->where('course', self::TARGET)
            ->update(['course' => 'BSiNT']);

        DB::table('coordinators')
            ->where('major', self::TARGET)
            ->update(['major' => 'BSiNT']);

        DB::table('coordinators')
            ->where('department', self::TARGET)
            ->update(['department' => 'BSiNT']);

        $programRows = DB::table('dynamic_options')
            ->where('type', 'program')
            ->whereIn('value', [self::TARGET, 'BSiNT'])
            ->orderBy('id')
            ->get(['id', 'value', 'is_active']);

        if ($programRows->isNotEmpty()) {
            $bsintRow = $programRows->firstWhere('value', 'BSiNT');

            if (! $bsintRow) {
                DB::table('dynamic_options')->insert([
                    'type' => 'program',
                    'value' => 'BSiNT',
                    'is_active' => (bool) ($programRows->first()->is_active ?? true),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('dynamic_options')
                    ->where('id', $bsintRow->id)
                    ->update(['is_active' => true, 'updated_at' => now()]);
            }

            DB::table('dynamic_options')
                ->where('type', 'program')
                ->where('value', self::TARGET)
                ->delete();
        }
    }
};
