<?php

namespace App\Support;

use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Student name/search helpers aligned with the Generate Report UI:
 * - * and % as wildcards, ? as single-character (SQL LIKE)
 * - Plain text: substring match on common fields plus fuzzy / “near” name matching (typos, spacing)
 */
class StudentSearch
{
    public static function buildWildcardTerm(string $search): string
    {
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $search);
        $wildcardReady = str_replace(['*', '?'], ['%', '_'], $escaped);

        if (! str_contains($wildcardReady, '%') && ! str_contains($wildcardReady, '_')) {
            return '%'.$wildcardReady.'%';
        }

        return $wildcardReady;
    }

    public static function usesWildcardSyntax(string $search): bool
    {
        return strpbrk($search, '*?%_') !== false;
    }

    /**
     * Broaden SQL so fuzzy post-filter can see near-name rows (e.g. Joemarie vs Joemary).
     *
     * @param  callable(Builder): void  $coreLike  Existing OR branches (name, student_no, …)
     */
    public static function applyBroadNameHints(Builder $query, string $trim, callable $coreLike): void
    {
        $query->where(function ($outer) use ($trim, $coreLike) {
            $outer->where(function ($q) use ($coreLike) {
                $coreLike($q);
            });
            $len = mb_strlen($trim);
            if ($len >= 3) {
                $outer->orWhere('name', 'like', '%'.mb_substr($trim, 0, 3).'%');
            } elseif ($len >= 2) {
                $outer->orWhere('name', 'like', mb_substr($trim, 0, 1).'%');
            }
        });
    }

    public static function applyCoordinatorDirectoryLike(Builder $q, string $term): void
    {
        $q->where('name', 'like', $term)
            ->orWhere('student_no', 'like', $term)
            ->orWhereHas('activeTermAssignment', function ($aq) use ($term) {
                $aq->where('course', 'like', $term)
                    ->orWhere('school_year', 'like', $term)
                    ->orWhere('term', 'like', $term)
                    ->orWhere('section', 'like', $term);
            });
    }

    public static function applyAdminStudentLike(Builder $q, string $term): void
    {
        $q->where('name', 'like', $term)
            ->orWhere('student_no', 'like', $term)
            ->orWhere('course', 'like', $term)
            ->orWhereHas('activeTermAssignment', function ($assignmentQuery) use ($term) {
                $assignmentQuery->where('course', 'like', $term)
                    ->orWhere('term', 'like', $term)
                    ->orWhere('section', 'like', $term)
                    ->orWhere('school_year', 'like', $term);
            });
    }

    public static function applyPendingVerificationLike(Builder $q, string $term): void
    {
        $q->where('name', 'like', $term)
            ->orWhere('student_no', 'like', $term)
            ->orWhere('course', 'like', $term);
    }

    public static function applyOjtCompletionLike(Builder $q, string $term): void
    {
        $q->where('name', 'like', $term)
            ->orWhere('student_no', 'like', $term)
            ->orWhereHas('activeTermAssignment', function ($aq) use ($term) {
                $aq->where('course', 'like', $term);
            });
    }

    public static function applyAttendanceLogsLike(Builder $q, string $term): void
    {
        $q->where('name', 'like', $term)
            ->orWhere('student_no', 'like', $term);
    }

    /**
     * After a broad SQL query, keep rows that truly match (substring or fuzzy name).
     *
     * @param  Collection<int, Student>  $students
     * @return Collection<int, Student>
     */
    public static function refinePlainSearch(Collection $students, string $search, bool $withAssignment = true): Collection
    {
        $search = trim($search);
        if ($search === '' || self::usesWildcardSyntax($search)) {
            return $students->values();
        }

        return $students
            ->filter(fn (Student $s) => self::plainOrFuzzyMatch($s, $search, $withAssignment))
            ->values();
    }

    public static function plainOrFuzzyMatch(Student $student, string $search, bool $withAssignment = true): bool
    {
        $search = trim($search);
        if ($search === '') {
            return true;
        }

        $term = self::buildWildcardTerm($search);
        $core = trim($term, '%');
        if (! str_contains($core, '%') && ! str_contains($core, '_')) {
            foreach (self::haystacks($student, $withAssignment) as $h) {
                if ($h !== '' && mb_stripos($h, $core) !== false) {
                    return true;
                }
            }
        }

        return self::fuzzyNameMatch((string) ($student->name ?? ''), $search);
    }

    /**
     * @return list<string>
     */
    public static function haystacks(Student $student, bool $withAssignment): array
    {
        $out = array_filter([
            (string) ($student->name ?? ''),
            (string) ($student->student_no ?? ''),
            (string) ($student->course ?? ''),
        ], fn (string $s) => $s !== '');

        if ($withAssignment) {
            $a = $student->activeTermAssignment;
            if ($a) {
                foreach ([$a->course ?? '', $a->school_year ?? '', $a->term ?? '', $a->section ?? ''] as $v) {
                    if ((string) $v !== '') {
                        $out[] = (string) $v;
                    }
                }
            }
        }

        return array_values(array_unique($out));
    }

    public static function fuzzyNameMatch(string $name, string $query): bool
    {
        $query = mb_strtolower(trim($query));
        if ($query === '') {
            return false;
        }

        $nameLower = mb_strtolower(trim($name));
        if ($nameLower === '') {
            return false;
        }

        if (mb_strpos($nameLower, $query) !== false) {
            return true;
        }

        $qCompact = preg_replace('/\s+/u', '', $query) ?? '';
        $compact = preg_replace('/\s+/u', '', $nameLower) ?? '';
        if ($qCompact !== '' && mb_strpos($compact, $qCompact) !== false) {
            return true;
        }

        foreach (preg_split('/\s+/u', trim($name)) ?: [] as $word) {
            $w = mb_strtolower($word);
            if ($w === '') {
                continue;
            }
            if (mb_strpos($w, $query) !== false || mb_strpos($query, $w) !== false) {
                return true;
            }
            if (self::stringsFuzzyClose($w, $query)) {
                return true;
            }
        }

        return $qCompact !== '' && self::stringsFuzzyClose($compact, $qCompact);
    }

    private static function stringsFuzzyClose(string $a, string $b): bool
    {
        $la = mb_strlen($a);
        $lb = mb_strlen($b);
        $max = max($la, $lb, 1);
        if ($max < 3) {
            return $a === $b;
        }

        $threshold = max(1, (int) floor($max * 0.28));

        if (self::isLikelyAscii($a) && self::isLikelyAscii($b) && $max <= 255) {
            return levenshtein($a, $b) <= $threshold;
        }

        similar_text($a, $b, $pct);

        return $pct >= 72.0;
    }

    private static function isLikelyAscii(string $s): bool
    {
        return $s === '' || preg_match('/^[\x00-\x7F]+$/', $s) === 1;
    }
}
