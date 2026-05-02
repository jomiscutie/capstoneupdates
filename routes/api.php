<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - NORSU OJT DTR
|--------------------------------------------------------------------------
|
| Access the backend from your phone or other devices. All routes are
| prefixed with /api and return JSON. Your database and .env are unchanged.
|
*/

Route::get('/health', function () {
    return response()->json([
        'app' => 'NORSU OJT DTR',
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
    ]);
});

Route::get('/', function (Request $request) {
    $base = $request->getSchemeAndHttpHost().'/api';

    return response()->json([
        'app' => 'NORSU OJT DTR',
        'message' => 'Backend is reachable. Use the same host for the web app.',
        'base_url' => $base,
        'endpoints' => [
            'GET '.$base.'/health' => 'Health check',
            'GET '.$base.'/' => 'This info',
        ],
        'web_login' => $request->getSchemeAndHttpHost().'/login',
    ]);
});
