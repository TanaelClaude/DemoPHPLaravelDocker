<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeekValidation extends Model
{
    protected $fillable = [
        'week_start',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'week_start' => 'date',
            'validated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
