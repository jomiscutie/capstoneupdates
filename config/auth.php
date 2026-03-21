<?php

return [
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'admin' => [
            'driver' => 'session',
            'provider' => 'admins',
        ],

        'coordinator' => [
            'driver' => 'session',
            'provider' => 'coordinators',
        ],

        'student' => [
            'driver' => 'session',
            'provider' => 'students',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        'admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\Admin::class,
        ],

        'coordinators' => [
            'driver' => 'eloquent',
            'model' => App\Models\Coordinator::class,
        ],

        'students' => [
            'driver' => 'eloquent',
            'model' => App\Models\Student::class,
        ],
    ],

    // ...existing code for passwords, password_timeout, etc.
];
