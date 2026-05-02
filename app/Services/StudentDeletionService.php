<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class StudentDeletionService
{
    public const MAX_BATCH = 40;

    /**
     * Soft-delete a student and append an audit log entry (caller may wrap in a transaction).
     */
    public static function deleteWithAudit(Student $student, string $actorType, int $actorId): void
    {
        $context = [
            'student_no' => $student->student_no,
            'name' => $student->name,
            'course' => $student->course,
            'major' => $student->major,
        ];
        $id = $student->id;
        $student->delete();

        AuditLog::create([
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'action' => 'student_soft_deleted',
            'target_type' => 'student',
            'target_id' => $id,
            'details' => 'Student archived (soft delete): '.$context['student_no'].' — '.$context['name'].'.',
            'context' => array_merge($context, ['soft_deleted_at' => now()->toIso8601String()]),
        ]);
    }

    /**
     * Delete many students in one transaction (all succeed or none).
     *
     * @param  iterable<int, Student>  $students
     */
    public static function deleteManyWithAudit(iterable $students, string $actorType, int $actorId): int
    {
        $list = collect($students)->values()->all();
        if ($list === []) {
            return 0;
        }

        return (int) DB::transaction(function () use ($list, $actorType, $actorId) {
            foreach ($list as $student) {
                self::deleteWithAudit($student, $actorType, $actorId);
            }

            return count($list);
        });
    }
}
