<?php

namespace App\Models;

use App\Support\ProgramAlias;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoordinatorAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'coordinator_id',
        'course',
        'school_year',
        'semester',
        'section',
    ];

    public function coordinator()
    {
        return $this->belongsTo(Coordinator::class);
    }

    public function setCourseAttribute($value): void
    {
        $this->attributes['course'] = ProgramAlias::normalizeCourse($value);
    }
}
