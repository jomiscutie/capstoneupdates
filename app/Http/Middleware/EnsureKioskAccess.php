<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureKioskAccess
{
    public function handle(Request $request, Closure $next)
    {
        $configuredKey = trim((string) config('dtr.kiosk_access_key', ''));
        if ($configuredKey === '') {
            return response()->json(['message' => 'Kiosk access key is not configured.'], 503);
        }

        $provided = trim((string) ($request->header('X-Kiosk-Key') ?? $request->query('kiosk_key', '')));
        if ($provided === '' || ! hash_equals($configuredKey, $provided)) {
            return response()->json(['message' => 'Unauthorized kiosk access.'], 401);
        }

        return $next($request);
    }
}
