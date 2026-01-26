<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix any incorrectly categorized time-ins
        // If time_in is 12:00 PM or later, move it to afternoon_time_in
        $attendances = \DB::table('attendances')
            ->whereNotNull('time_in')
            ->get();

        foreach ($attendances as $attendance) {
            $timeIn = $attendance->time_in;
            if ($timeIn) {
                $hour = (int) date('H', strtotime($timeIn));
                
                // If time_in is 12:00 PM (12) or later, it should be in afternoon_time_in
                if ($hour >= 12) {
                    \DB::table('attendances')
                        ->where('id', $attendance->id)
                        ->update([
                            'afternoon_time_in' => $timeIn,
                            'afternoon_is_late' => $attendance->is_late ?? false,
                            'afternoon_late_minutes' => $attendance->late_minutes ?? null,
                            'time_in' => null,
                            'is_late' => false,
                            'late_minutes' => null,
                        ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration fixes data, so there's no safe way to reverse it
        // The data correction is one-way
    }
};
