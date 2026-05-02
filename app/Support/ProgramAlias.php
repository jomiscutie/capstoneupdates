<?php

namespace App\Support;

class ProgramAlias
{
    public const BSINT = 'Bachelor of Science in Information Technology';

    /**
     * Normalize known program/course variants into canonical labels.
     */
    public static function normalizeCourse(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return $trimmed;
        }

        $canonical = self::canonicalize($trimmed);

        $bsintAliases = [
            'BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY',
            'INFORMATION TECHNOLOGY',
            'BSIT',
            'BSINT',
        ];

        if (in_array($canonical, $bsintAliases, true)) {
            return self::BSINT;
        }

        return $trimmed;
    }

    public static function canonicalize(string $value): string
    {
        $collapsed = preg_replace('/\s+/', ' ', trim($value));
        $collapsed = $collapsed === null ? '' : $collapsed;

        return mb_strtoupper($collapsed, 'UTF-8');
    }
}
