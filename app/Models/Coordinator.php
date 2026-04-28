<?php

namespace App\Models;

use App\Support\ProgramAlias;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
