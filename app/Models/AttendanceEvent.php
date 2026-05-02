<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'student_id',
        'manual_attendance_request_id',
        'event_type',
        'event_direction',
        'occurred_at',
        'source',
        'verification_method',
        'snapshot_path',
        'meta',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'meta' => 'array',
    ];

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function manualAttendanceRequest(): BelongsTo
    {
        return $this->belongsTo(ManualAttendanceRequest::class);
    }
}
