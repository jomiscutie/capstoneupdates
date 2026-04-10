<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

class Student extends Authenticatable
{
    use Notifiable;

    public const TERMS = StudentTermAssignment::TERMS;
    public const ASSIGNMENT_TERMS = StudentTermAssignment::ASSIGNMENT_TERMS;
    public const ASSIGNMENT_SEMESTERS = StudentTermAssignment::ASSIGNMENT_TERMS;
    public const SECTIONS = StudentTermAssignment::SECTIONS;
    public const ASSIGNMENT_SECTIONS = StudentTermAssignment::ASSIGNMENT_SECTIONS;

    public static function hasVerificationColumn(): bool
    {
        return Schema::hasColumn((new static)->getTable(), 'verification_status');
    }

    protected $fillable = [
        'student_no',
        'name',
        'department',
        'major',
        'course',
        'semester',
        'section',
        'password',
        'face_encoding',
        'verification_status',
        'verified_at',
        'verified_by',
        'rejected_at',
        'rejected_by',
        'required_ojt_hours',
        'ojt_completion_confirmed_at',
        'ojt_confirmed_by',
        'current_session_id',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'rejected_at' => 'datetime',
        'ojt_completion_confirmed_at' => 'datetime',
        'required_ojt_hours' => 'float',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $guard = 'student';

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function termAssignments()
    {
        return $this->hasMany(StudentTermAssignment::class);
    }

    public function activeTermAssignment()
    {
        return $this->hasOne(StudentTermAssignment::class)
            ->where('status', StudentTermAssignment::STATUS_ACTIVE)
            ->latestOfMany('id');
    }

    public function ojtConfirmedBy()
    {
        return $this->belongsTo(Coordinator::class, 'ojt_confirmed_by');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(Coordinator::class, 'verified_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(Coordinator::class, 'rejected_by');
    }

    public function isVerified(): bool
    {
        if (! static::hasVerificationColumn()) {
            return true;
        }

        return $this->verification_status === 'verified';
    }

    public function isPendingVerification(): bool
    {
        if (! static::hasVerificationColumn()) {
            return false;
        }

        return $this->verification_status === 'pending';
    }

    public function isRejected(): bool
    {
        if (! static::hasVerificationColumn()) {
            return false;
        }

        return $this->verification_status === 'rejected';
    }

    public function scopeForCoordinator($query, $coordinator)
    {
        $assignments = method_exists($coordinator, 'assignments')
            ? $coordinator->assignments()->get(['course', 'school_year', 'semester', 'section'])
            : collect();

        if ($assignments->isNotEmpty()) {
            return $query->whereHas('activeTermAssignment', function ($termQuery) use ($assignments) {
                $termQuery->where(function ($studentQuery) use ($assignments) {
                    foreach ($assignments as $assignment) {
                        $studentQuery->orWhere(function ($matchQuery) use ($assignment) {
                            // Match course - use LIKE for flexible matching (e.g., "INFORMATION TECHNOLOGY" matches "Bachelor of Science in Information Technology")
                            $matchQuery->where('course', 'like', '%' . $assignment->course . '%');

                            // School year matching - if assignment has school_year, match it; otherwise match any
                            if (! empty($assignment->school_year)) {
                                $matchQuery->where('school_year', $assignment->school_year);
                            }
                            // If school_year is NULL/empty in assignment, don't filter by school_year (match all)

                            if ($assignment->semester !== 'All') {
                                $matchQuery->where('term', $assignment->semester);
                            }

                            if ($assignment->section !== 'All') {
                                $matchQuery->where('section', $assignment->section);
                            }
                        });
                    }
                });
            });
        }

        if (empty($coordinator->major)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('course', 'like', '%' . $coordinator->major . '%');
    }

    public function isVisibleToCoordinator($coordinator): bool
    {
        return static::query()
            ->whereKey($this->id)
            ->forCoordinator($coordinator)
            ->exists();
    }

    public function getDisplayCourseAttribute(): ?string
    {
        return $this->activeTermAssignment?->course ?: $this->course;
    }

    public function getDisplayTermAttribute(): ?string
    {
        return $this->activeTermAssignment?->term;
    }

    public function getDisplaySectionAttribute(): ?string
    {
        return $this->activeTermAssignment?->section ?: $this->section;
    }

    public function getCurrentRequiredHoursAttribute(): float
    {
        return $this->requiredHoursForAssignment();
    }

    public function completionConfirmationForAssignment(?StudentTermAssignment $assignment = null): ?StudentTermAssignment
    {
        $assignment ??= $this->activeTermAssignment;

        if ($assignment && $assignment->confirmed_at) {
            return $assignment;
        }

        return null;
    }

    public function scopeVerified($query)
    {
        if (! static::hasVerificationColumn()) {
            return $query;
        }

        return $query->where('verification_status', 'verified');
    }

    public function scopePendingVerification($query)
    {
        if (! static::hasVerificationColumn()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('verification_status', 'pending');
    }

    public function getTotalRenderedHoursAttribute(): float
    {
        return $this->renderedHoursForAssignment();
    }

    public static function minutesFromRenderedHours(?string $value): int
    {
        return self::parseHoursRenderedToMinutes($value);
    }

    public function renderedHoursForAssignment(?StudentTermAssignment $assignment = null): float
    {
        $assignment ??= $this->activeTermAssignment;

        $query = $this->attendances()->whereNotNull('hours_rendered');

        if ($assignment?->started_at) {
            $query->whereDate('date', '>=', $assignment->started_at->toDateString());
        }

        if ($assignment?->completed_at) {
            $query->whereDate('date', '<=', $assignment->completed_at->toDateString());
        }

        $totalMinutes = $query->get()->sum(function ($att) {
            return self::parseHoursRenderedToMinutes($att->hours_rendered);
        });

        return round($totalMinutes / 60, 2);
    }

    public function requiredHoursForAssignment(?StudentTermAssignment $assignment = null): float
    {
        $assignment ??= $this->activeTermAssignment;

        return (float) ($assignment?->required_ojt_hours ?? $this->required_ojt_hours ?? 120);
    }

    public function hasReachedRequiredHours(?StudentTermAssignment $assignment = null): bool
    {
        $assignment ??= $this->activeTermAssignment;

        return $this->renderedHoursForAssignment($assignment) >= $this->requiredHoursForAssignment($assignment);
    }

    public function isOjtCompletionConfirmed(?StudentTermAssignment $assignment = null): bool
    {
        return $this->completionConfirmationForAssignment($assignment) !== null;
    }

    private static function parseHoursRenderedToMinutes(?string $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (preg_match('/^\s*(\d+)\s*hr\s*(\d+)\s*min\s*$/i', $value, $m)) {
            return (int) $m[1] * 60 + (int) $m[2];
        }

        if (preg_match('/^\s*(\d+)\s*hr\s*$/i', $value, $m)) {
            return (int) $m[1] * 60;
        }

        return 0;
    }
}
