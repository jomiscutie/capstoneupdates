<?php

namespace App\Support;

use Carbon\Carbon;

class WeekRangeFilter
{
    public static function defaultInputs(?string $startWeek = null, ?string $endWeek = null, ?string $legacyWeek = null): array
    {
        $currentWeek = now()->format('o').'-W'.str_pad((string) now()->isoWeek(), 2, '0', STR_PAD_LEFT);

        $startWeek = $startWeek ?: $legacyWeek ?: $currentWeek;
        $endWeek = $endWeek ?: $legacyWeek ?: $startWeek;

        return [$startWeek, $endWeek];
    }

    public static function parse(?string $startWeek, ?string $endWeek): ?array
    {
        if (empty($startWeek)) {
            return null;
        }

        $endWeek = $endWeek ?: $startWeek;

        $start = self::parseWeek($startWeek);
        $end = self::parseWeek($endWeek);

        if (! $start || ! $end) {
            return null;
        }

        if ($end->lt($start)) {
            [$start, $end] = [$end, $start];
            [$startWeek, $endWeek] = [$endWeek, $startWeek];
        }

        $sameWeek = $startWeek === $endWeek;

        if ($sameWeek) {
            $labelPrefix = 'Week '.$start->isoWeek().', '.$start->isoWeekYear;
        } elseif ($start->isoWeekYear === $end->isoWeekYear) {
            $labelPrefix = 'Weeks '.$start->isoWeek().' - '.$end->isoWeek().', '.$start->isoWeekYear;
        } else {
            $labelPrefix = 'Weeks '.$start->isoWeek().', '.$start->isoWeekYear.' - '.$end->isoWeek().', '.$end->isoWeekYear;
        }

        return [
            'start_input' => $startWeek,
            'end_input' => $endWeek,
            'start_date' => $start->copy()->startOfWeek()->format('Y-m-d'),
            'end_date' => $end->copy()->endOfWeek()->format('Y-m-d'),
            'label' => $labelPrefix.' ('.$start->format('M j').' - '.$end->copy()->endOfWeek()->format('M j, Y').')',
        ];
    }

    private static function parseWeek(string $value): ?Carbon
    {
        if (! preg_match('/^(\d{4})-W(\d{2})$/', $value, $matches)) {
            return null;
        }

        return Carbon::now()->setISODate((int) $matches[1], (int) $matches[2])->startOfWeek();
    }
}
