<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DynamicOption extends Model
{
    public const TYPE_PROGRAM = 'program';

    public const TYPE_SECTION = 'section';

    protected $fillable = [
        'type',
        'value',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'bool',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
