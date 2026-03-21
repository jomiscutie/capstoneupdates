<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Face Recognition (Student Registration)
    |--------------------------------------------------------------------------
    | FACE_SAME_PERSON_THRESHOLD: Euclidean distance below which two faces are
    | considered the same person. Lower = stricter (fewer false positives).
    | Default 0.5. Use 0.45 if still getting false positives.
    |
    | FACE_DUPLICATE_CHECK: Set to false to disable the "same face, different
    | account" check entirely (e.g. if false positives persist).
    */
    'face_same_person_threshold' => env('FACE_SAME_PERSON_THRESHOLD', 0.5),
    'face_duplicate_check' => env('FACE_DUPLICATE_CHECK', true),

];
