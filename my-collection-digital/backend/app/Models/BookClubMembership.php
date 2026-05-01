<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookClubMembership extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_club_id',
        'user_id',
        'role',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(BookClub::class, 'book_club_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
