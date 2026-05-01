<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRetentionSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'daily_goal_pages',
        'weekly_goal_pages',
        'reminders_enabled',
        'reminder_hour',
        'timezone',
    ];

    protected $casts = [
        'reminders_enabled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
