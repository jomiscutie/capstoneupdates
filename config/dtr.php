<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Time-out cooldown (minutes)
    |--------------------------------------------------------------------------
    |
    | Minimum minutes after the student's latest time-in (morning or afternoon)
    | before they may record time-out. Server and dashboard UI both use this.
    |
    | Set in .env: DTR_TIME_OUT_COOLDOWN_MINUTES=30
    |
    */
    'time_out_cooldown_minutes' => max(0, (int) env('DTR_TIME_OUT_COOLDOWN_MINUTES')),

    /*
    |--------------------------------------------------------------------------
    | Default required OJT hours
    |--------------------------------------------------------------------------
    |
    | Initial required hours assigned to a student term at registration and
    | fallback default used in forms/displays when no explicit value exists.
    |
    | Set in .env: DTR_DEFAULT_REQUIRED_HOURS=120
    |
    */
    'default_required_hours' => max(1, (float) env('DTR_DEFAULT_REQUIRED_HOURS', 300)),

    /*
    |--------------------------------------------------------------------------
    | Password fallback review policy
    |--------------------------------------------------------------------------
    |
    | If a student uses password fallback this many times within the review
    | window, show a warning that supervised face re-enrollment is required.
    |
    | Set in .env:
    | DTR_PASSWORD_FALLBACK_REVIEW_THRESHOLD=3
    | DTR_PASSWORD_FALLBACK_REVIEW_WINDOW_DAYS=7
    |
    */
    'password_fallback_review_threshold' => max(1, (int) env('DTR_PASSWORD_FALLBACK_REVIEW_THRESHOLD', 3)),
    'password_fallback_review_window_days' => max(1, (int) env('DTR_PASSWORD_FALLBACK_REVIEW_WINDOW_DAYS', 7)),

    /*
    |--------------------------------------------------------------------------
    | Manual attendance request window (days)
    |--------------------------------------------------------------------------
    |
    | Students can request a manual attendance entry for up to this many days
    | back from today. This supports logbook/power interruption recovery while
    | avoiding very old retroactive requests.
    |
    | Set in .env: DTR_MANUAL_REQUEST_MAX_DAYS_BACK=14
    |
    */
    'manual_request_max_days_back' => max(1, (int) env('DTR_MANUAL_REQUEST_MAX_DAYS_BACK', 14)),

    /*
    |--------------------------------------------------------------------------
    | Kiosk access key
    |--------------------------------------------------------------------------
    |
    | Shared secret used by kiosk station routes. Send via X-Kiosk-Key header
    | (or kiosk_key query while testing). Keep this value private.
    |
    */
    'kiosk_access_key' => (string) env('DTR_KIOSK_ACCESS_KEY', ''),

];
