<?php

namespace App\Models;

use App\Support\ProgramAlias;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentTermAssignment extends Model
{
    use HasFactory;

    public const TERMS = ['Summer', '1st Semester', '2nd Semester'];

    public const ASSIGNMENT_TERMS = ['All', 'Summer', '1st Semester', '2nd Semester'];

    public const SECTIONS = ['A', 'B', 'C', 'D'];

    public const ASSIGNMENT_SECTIONS = ['All', 'A', 'B', 'C', 'D'];

    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'student_id',
        'course',
        'school_year',
        'term',
        'section',
        'required_ojt_hours',
        'confirmed_at',
        'confirmed_by',
        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'required_ojt_hours' => 'float',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function confirmedBy()
    {
        return $this->belongsTo(Coordinator::class, 'confirmed_by');
    }

    public function setCourseAttribute($value): void
    {
        $this->attributes['course'] = ProgramAlias::normalizeCourse($value);
    }
}
