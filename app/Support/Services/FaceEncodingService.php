<?php

namespace App\Support\Services;

class FaceEncodingService
{
    /**
     * Threshold below which two encodings are considered the same person (Euclidean distance).
     * Lower = stricter (fewer false positives). Same person typically 0.2–0.45; different people usually > 0.5.
     * 0.5 reduces false positives where different people are wrongly flagged.
     */
    public const SAME_PERSON_THRESHOLD = 0.5;

    /** Expected face descriptor length (face-api.js). */
    public const ENCODING_LENGTH = 128;

    /**
     * Calculate Euclidean distance between two face encodings.
     * Lower distance = more similar. Same person typically &lt; 0.5.
     */
    public static function distance(array $encoding1, array $encoding2): float
    {
        if (count($encoding1) !== count($encoding2) || count($encoding1) !== self::ENCODING_LENGTH) {
            return 999.0;
        }

        $sum = 0;
        for ($i = 0; $i < count($encoding1); $i++) {
            $diff = $encoding1[$i] - $encoding2[$i];
            $sum += $diff * $diff;
        }

        $distance = sqrt($sum);

        $variance = 0;
        foreach ($encoding1 as $val) {
            $variance += abs($val);
        }
        if (count($encoding1) > 0 && ($variance / count($encoding1)) < 0.001) {
            return 999.0;
        }

        return $distance;
    }

    /**
     * Returns true if the two encodings are considered the same person.
     * Threshold can be overridden via FACE_SAME_PERSON_THRESHOLD env (e.g. 0.45 for stricter).
     */
    public static function isSamePerson(array $encoding1, array $encoding2, ?float $threshold = null): bool
    {
        $threshold ??= (float) (config('services.face_same_person_threshold') ?? self::SAME_PERSON_THRESHOLD);

        return self::distance($encoding1, $encoding2) <= $threshold;
    }
}
