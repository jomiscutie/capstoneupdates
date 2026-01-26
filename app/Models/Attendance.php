<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'date',
        'time_in',
        'afternoon_time_in',
        'time_out',
        'hours_rendered',
        'is_late',
        'late_minutes',
        'afternoon_is_late',
        'afternoon_late_minutes',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
