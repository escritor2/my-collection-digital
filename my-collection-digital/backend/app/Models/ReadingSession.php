<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_book_id',
        'started_at',
        'ended_at',
        'duration_minutes',
        'pages_read',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userBook(): BelongsTo
    {
        return $this->belongsTo(UserBook::class);
    }
}
