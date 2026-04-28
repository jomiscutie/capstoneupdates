<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    use HasFactory;

    /** 12-hour format for display (e.g. 2:30 PM). */
    public const TIME_12_FORMAT = 'g:i A';

    protected $fillable = [
        'student_id',
        'date',
        'time_in',
        'afternoon_time_in',
        'lunch_break_out',
        'time_out',
        'hours_rendered',
        'is_late',
        'late_minutes',
        'afternoon_is_late',
        'afternoon_late_minutes',
        'verification_snapshot',
        'afternoon_verification_snapshot',
        'timeout_verification_snapshot',
        'is_invalid',
        'invalidated_at',
        'invalidated_by',
        'invalidation_reason',
        'invalidation_status',
        'invalidation_requested_at',
        'invalidation_reviewed_by',
        'invalidation_reviewed_at',
        'invalidation_review_note',
    ];

    protected $casts = [
        'is_invalid' => 'boolean',
        'invalidated_at' => 'datetime',
        'invalidation_requested_at' => 'datetime',
        'invalidation_reviewed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function invalidatedByCoordinator(): BelongsTo
    {
        return $this->belongsTo(Coordinator::class, 'invalidated_by');
    }

    public function events(): HasMany
    {
        return $this->hasMany(AttendanceEvent::class);
    }

    public function scopeValid($query)
    {
        return $query->where('is_invalid', false);
    }

    /** Time in, 12-hour format (e.g. 8:00 AM), or null. */
    public function getTimeIn12Attribute(): ?string
    {
        return $this->time_in ? Carbon::parse($this->time_in)->format(self::TIME_12_FORMAT) : null;
    }

    /** Afternoon time in, 12-hour format, or null. */
    public function getAfternoonTimeIn12Attribute(): ?string
    {
        return $this->afternoon_time_in ? Carbon::parse($this->afternoon_time_in)->format(self::TIME_12_FORMAT) : null;
    }

    /** Time out, 12-hour format, or null. */
    public function getTimeOut12Attribute(): ?string
    {
        return $this->time_out ? Carbon::parse($this->time_out)->format(self::TIME_12_FORMAT) : null;
    }

    /** Lunch break out, 12-hour format, or null. */
    public function getLunchBreakOut12Attribute(): ?string
    {
        $val = $this->attributes['lunch_break_out'] ?? null;

        return $val ? Carbon::parse($val)->format(self::TIME_12_FORMAT) : null;
    }

    /** Format minutes as "X hr Y min" for display (e.g. 90 → "1 hr 30 min"). */
    public static function minutesToHoursMinutes(int $minutes): string
    {
        if ($minutes <= 0) {
            return '0 min';
        }
        $h = (int) floor($minutes / 60);
        $m = $minutes % 60;
        if ($h > 0 && $m > 0) {
            return $h.' hr '.$m.' min';
        }
        if ($h > 0) {
            return $h.' hr';
        }

        return $m.' min';
    }

    /** Hours rendered normalized for display (handles previously saved negative values). */
    public function getHoursRenderedDisplayAttribute(): ?string
    {
        $raw = trim((string) ($this->hours_rendered ?? ''));
        if ($raw === '') {
            return null;
        }

        if (preg_match('/^\s*(-?\d+)\s*hr\s*(-?\d+)\s*min\s*$/i', $raw, $m)) {
            $hours = abs((int) $m[1]);
            $minutes = abs((int) $m[2]);

            return "{$hours} hr {$minutes} min";
        }

        if (preg_match('/^\s*(-?\d+)\s*hr\s*$/i', $raw, $m)) {
            $hours = abs((int) $m[1]);

            return "{$hours} hr";
        }

        return $raw;
    }

    /** Morning late duration as "X hr Y min". */
    public function getLateDisplayAttribute(): string
    {
        return self::minutesToHoursMinutes((int) ($this->late_minutes ?? 0));
    }

    /** Afternoon late duration as "X hr Y min". */
    public function getAfternoonLateDisplayAttribute(): string
    {
        return self::minutesToHoursMinutes((int) ($this->afternoon_late_minutes ?? 0));
    }
}
