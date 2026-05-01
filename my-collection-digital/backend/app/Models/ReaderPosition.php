<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReaderPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_book_id',
        'format',
        'locator',
        'percentage',
    ];

    protected $casts = [
        'locator' => 'array',
        'percentage' => 'decimal:2',
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
