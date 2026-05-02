<?php

namespace App\Models;

use App\Support\ProgramAlias;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class Coordinator extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guard = 'coordinator';

    protected $fillable = [
        'name',
        'email',
        'student_id',
        'department',
        'college',
        'major',
        'password',
        'role',
        'current_session_id',
        'is_active',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    private const COLLEGE_ALIASES = [
        'COLLEGE OF ARTS AND SCIENCES' => 'CAS',
        'CAS' => 'CAS',
    ];

    public function assignments()
    {
        return $this->hasMany(CoordinatorAssignment::class);
    }

    /**
     * Distinct normalized program names from coordinator_assignments rows, or legacy single major().
     *
     * @return Collection<int, string>
     */
    public function assignedProgramLabels(): Collection
    {
        $fromAssignments = $this->assignments()
            ->pluck('course')
            ->map(fn ($course) => ProgramAlias::normalizeCourse(trim((string) $course)))
            ->filter(fn ($value) => is_string($value) && $value !== '')
            ->unique()
            ->sort()
            ->values();

        if ($fromAssignments->isNotEmpty()) {
            return $fromAssignments;
        }

        $major = trim((string) ($this->major ?? ''));
        if ($major !== '') {
            return collect([ProgramAlias::normalizeCourse($major)])->filter();
        }

        return collect();
    }

    public function setMajorAttribute($value): void
    {
        $this->attributes['major'] = ProgramAlias::normalizeCourse($value);
    }

    public function setDepartmentAttribute($value): void
    {
        $this->attributes['department'] = ProgramAlias::normalizeCourse($value);
    }

    public function setCollegeAttribute($value): void
    {
        $this->attributes['college'] = self::normalizeCollege($value);
    }

    public function getCollegeAttribute($value): ?string
    {
        $normalized = self::normalizeCollege($value);
        return $normalized !== '' ? $normalized : null;
    }

    private static function normalizeCollege($value): string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return '';
        }

        $canonical = strtoupper(preg_replace('/\s+/', ' ', $raw) ?? $raw);
        return self::COLLEGE_ALIASES[$canonical] ?? $raw;
    }
}
