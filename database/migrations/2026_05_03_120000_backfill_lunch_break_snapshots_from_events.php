<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Copy lunch break verification paths from attendance_events into attendances.lunch_break_verification_snapshot
     * for rows where the dedicated column was never filled (feature added after events began storing snapshots).
     */
    public function up(): void
    {
        if (! Schema::hasColumn('attendances', 'lunch_break_verification_snapshot')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('
                UPDATE attendances a
                INNER JOIN (
                    SELECT ae.attendance_id, ae.snapshot_path
                    FROM attendance_events ae
                    INNER JOIN (
                        SELECT attendance_id, MAX(id) AS max_id
                        FROM attendance_events
                        WHERE event_type = ?
                          AND snapshot_path IS NOT NULL
                          AND attendance_id IS NOT NULL
                        GROUP BY attendance_id
                    ) latest ON latest.max_id = ae.id
                ) src ON src.attendance_id = a.id
                SET a.lunch_break_verification_snapshot = src.snapshot_path
                WHERE a.lunch_break_verification_snapshot IS NULL
                  AND a.lunch_break_out IS NOT NULL
            ', ['lunch_break_out']);

            return;
        }

        $maxIds = DB::table('attendance_events')
            ->selectRaw('MAX(id) as id')
            ->where('event_type', 'lunch_break_out')
            ->whereNotNull('snapshot_path')
            ->whereNotNull('attendance_id')
            ->groupBy('attendance_id')
            ->pluck('id');

        if ($maxIds->isEmpty()) {
            return;
        }

        $rows = DB::table('attendance_events')
            ->whereIn('id', $maxIds)
            ->get(['id', 'attendance_id', 'snapshot_path']);

        foreach ($rows as $row) {
            DB::table('attendances')
                ->where('id', $row->attendance_id)
                ->whereNull('lunch_break_verification_snapshot')
                ->whereNotNull('lunch_break_out')
                ->update(['lunch_break_verification_snapshot' => $row->snapshot_path]);
        }
    }

    public function down(): void
    {
        // Irreversible: cannot know which values were backfilled vs set by the app.
    }
};
