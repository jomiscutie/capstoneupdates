<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable; // ✅ not Model
use Illuminate\Notifications\Notifiable;

class Student extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'student_no',
        'name',
        'department',
        'major',
        'course',
        'password',
        'face_encoding',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $guard = 'student';
}
