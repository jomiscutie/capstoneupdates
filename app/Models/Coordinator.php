<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Coordinator extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guard = 'coordinator';

    protected $fillable = [
        'name',
        'email',
        'student_id',
        'department',
        'college',
        'major',
        'password',
        'role',
        'current_session_id',
        'is_active',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function assignments()
    {
        return $this->hasMany(CoordinatorAssignment::class);
    }
}
