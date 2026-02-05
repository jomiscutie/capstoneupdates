<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable; // ✅ not Model
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

class Student extends Authenticatable
{
    use Notifiable;

    /** True if the coordinator verification migration has been run. */
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

    /** Whether the student has been verified by a coordinator and can use the system. */
    public function isVerified(): bool
    {
        if (! static::hasVerificationColumn()) {
            return true; // Before migration: treat all as verified
        }
        return $this->verification_status === 'verified';
    }

    /** Whether the student is pending coordinator verification. */
    public function isPendingVerification(): bool
    {
        if (! static::hasVerificationColumn()) {
            return false;
        }
        return $this->verification_status === 'pending';
    }

    /** Whether the student was rejected by a coordinator. */
    public function isRejected(): bool
    {
        if (! static::hasVerificationColumn()) {
            return false;
        }
        return $this->verification_status === 'rejected';
    }

    /**
     * Scope: only students belonging to the coordinator's program.
     */
    public function scopeForCoordinator($query, $coordinator)
    {
        if (empty($coordinator->major)) {
            return $query->whereRaw('1 = 0');
        }
        return $query->where('course', $coordinator->major);
    }

    /** Scope: only students verified by a coordinator. */
    public function scopeVerified($query)
    {
        if (! static::hasVerificationColumn()) {
            return $query;
        }
        return $query->where('verification_status', 'verified');
    }

    /** Scope: only students pending verification. */
    public function scopePendingVerification($query)
    {
        if (! static::hasVerificationColumn()) {
            return $query->whereRaw('1 = 0'); // No pending if column missing
        }
        return $query->where('verification_status', 'pending');
    }

    /**
     * Total hours rendered (decimal) from all attendance records.
     * Parses "X hr Y min" per record and sums.
     */
    public function getTotalRenderedHoursAttribute(): float
    {
        $totalMinutes = $this->attendances()
            ->whereNotNull('hours_rendered')
            ->get()
            ->sum(function ($att) {
                return self::parseHoursRenderedToMinutes($att->hours_rendered);
            });
        return round($totalMinutes / 60, 2);
    }

    public function hasReachedRequiredHours(): bool
    {
        return $this->total_rendered_hours >= (float) $this->required_ojt_hours;
    }

    public function isOjtCompletionConfirmed(): bool
    {
        return $this->ojt_completion_confirmed_at !== null;
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
