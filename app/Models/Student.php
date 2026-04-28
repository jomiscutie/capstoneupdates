<?php

namespace App\Models;

use App\Support\ProgramAlias;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

class Student extends Authenticatable
{
    use Notifiable, SoftDeletes;

    public const TERMS = StudentTermAssignment::TERMS;

    public const ASSIGNMENT_TERMS = StudentTermAssignment::ASSIGNMENT_TERMS;

    public const ASSIGNMENT_SEMESTERS = StudentTermAssignment::ASSIGNMENT_TERMS;

    public const SECTIONS = StudentTermAssignment::SECTIONS;

    public const ASSIGNMENT_SECTIONS = StudentTermAssignment::ASSIGNMENT_SECTIONS;

    public const PROGRAM_CATALOG = [
        'COLLEGE OF ARTS AND SCIENCES' => [
            'Bachelor of Arts',
            'Bachelor of Science in Biology',
            'Bachelor of Science in Chemistry',
            'Bachelor of Science in Computer Science',
            'Bachelor of Science in Geology',
            'Bachelor of Science in Information Technology',
            'Bachelor of Science in Mathematics',
            'Bachelor of Science in Psychology',
        ],
        'COLLEGE OF CRIMINAL JUSTICE' => [
            'Bachelor of Science in Criminology',
        ],
        'COLLEGE OF BUSINESS ADMINISTRATION' => [
            'Associate in Secretarial Education',
            'Bachelor of Science in Accountancy',
            'Bachelor of Science in Business Administration',
            'Bachelor of Science in Office Systems Management',
            'Associate in Hospitality Management',
            'Bachelor of Science in Hospitality Management',
            'Bachelor of Science in Tourism',
        ],
        'COLLEGE OF EDUCATION' => [
            'Bachelor of Elementary Education',
            'Bachelor of Secondary Education',
        ],
    ];

    public const PROGRAMS = [
        'Bachelor of Arts',
        'Bachelor of Science in Biology',
        'Bachelor of Science in Chemistry',
        'Bachelor of Science in Computer Science',
        'Bachelor of Science in Geology',
        'Bachelor of Science in Information Technology',
        'Bachelor of Science in Mathematics',
        'Bachelor of Science in Psychology',
        'Bachelor of Science in Criminology',
        'Associate in Secretarial Education',
        'Bachelor of Science in Accountancy',
        'Bachelor of Science in Business Administration',
        'Bachelor of Science in Office Systems Management',
        'Associate in Hospitality Management',
        'Bachelor of Science in Hospitality Management',
        'Bachelor of Science in Tourism',
        'Bachelor of Elementary Education',
        'Bachelor of Secondary Education',
    ];

    public const PROGRAM_MAJORS = [
        'Bachelor of Arts' => [
            'Mass Communication',
            'Literature',
            'Political Science',
            'English',
            'Social Science',
            'History',
            'Mathematics',
        ],
        'Bachelor of Science in Business Administration' => [
            'Management',
            'Marketing',
            'Business Finance Management',
        ],
        'Bachelor of Elementary Education' => [
            'Special Education',
            'Preschool',
            'General Curriculum',
        ],
        'Bachelor of Secondary Education' => [
            'English',
            'Math',
            'Filipino',
            'Social Science',
            'Biological Science',
            'Physical Science',
            'TLE',
            'Values Ed',
            'MAPEH',
        ],
    ];

    public static function majorsForProgram(string $program): array
    {
        return self::PROGRAM_MAJORS[$program] ?? [];
    }

    public static function hasMajorsForProgram(string $program): bool
    {
        return ! empty(self::majorsForProgram($program));
    }

    public static function getProgramOptions(): array
    {
        $options = self::PROGRAMS;

        if (Schema::hasTable('dynamic_options')) {
            $dynamicPrograms = DynamicOption::query()
                ->active()
                ->where('type', DynamicOption::TYPE_PROGRAM)
                ->orderBy('value')
                ->pluck('value')
                ->all();

            $options = array_merge($options, $dynamicPrograms);
        }

        $options = array_values(array_unique(array_map(static fn ($value) => trim((string) $value), $options)));
        sort($options);

        return $options;
    }

    public static function getSectionOptions(): array
    {
        $options = self::SECTIONS;

        if (Schema::hasTable('dynamic_options')) {
            $dynamicSections = DynamicOption::query()
                ->active()
                ->where('type', DynamicOption::TYPE_SECTION)
                ->orderBy('value')
                ->pluck('value')
                ->all();

            $options = array_merge($options, $dynamicSections);
        }

        $options = array_values(array_unique(array_map(static fn ($value) => trim((string) $value), $options)));
        sort($options);

        return $options;
    }

    public static function getProgramCatalog(): array
    {
        $catalog = self::PROGRAM_CATALOG;
        $catalogPrograms = [];

        foreach ($catalog as $programs) {
            foreach ($programs as $program) {
                $catalogPrograms[] = trim((string) $program);
            }
        }

        $dynamicOnlyPrograms = array_values(array_diff(self::getProgramOptions(), $catalogPrograms));

        if ($dynamicOnlyPrograms !== []) {
            $catalog['ADDITIONAL PROGRAMS'] = $dynamicOnlyPrograms;
        }

        return $catalog;
    }

    public static function hasVerificationColumn(): bool
    {
        return Schema::hasColumn((new static)->getTable(), 'verification_status');
    }

    protected $fillable = [
        'student_no',
        'name',
        'last_name',
        'first_name',
        'middle_name',
        'suffix',
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

    public function setCourseAttribute($value): void
    {
        $this->attributes['course'] = ProgramAlias::normalizeCourse($value);
    }

    public function setMajorAttribute($value): void
    {
        $this->attributes['major'] = ProgramAlias::normalizeCourse($value);
    }

    public function manualAttendanceRequests()
    {
        return $this->hasMany(ManualAttendanceRequest::class);
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
                            $matchQuery->where('course', 'like', '%'.$assignment->course.'%');

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

        return $query->where('course', 'like', '%'.$coordinator->major.'%');
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

        $query = $this->attendances()->valid()->whereNotNull('hours_rendered');

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

        return (float) ($assignment?->required_ojt_hours ?? $this->required_ojt_hours ?? config('dtr.default_required_hours', 120));
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

        if (preg_match('/^\s*(-?\d+)\s*hr\s*(-?\d+)\s*min\s*$/i', $value, $m)) {
            return abs((int) $m[1]) * 60 + abs((int) $m[2]);
        }

        if (preg_match('/^\s*(-?\d+)\s*hr\s*$/i', $value, $m)) {
            return abs((int) $m[1]) * 60;
        }

        return 0;
    }
}
