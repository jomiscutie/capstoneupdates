<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        if ($request->expectsJson()) {
            return null;
        }

        if ($request->is('coordinator/*')) {
            return route('coordinator.login');
        }

        if ($request->is('student/*')) {
            return route('student.login');
        }

        return route('login');
    }
}
