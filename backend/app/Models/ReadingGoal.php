<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'target_value',
        'target_unit',
        'period_start',
        'period_end',
        'is_active',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}