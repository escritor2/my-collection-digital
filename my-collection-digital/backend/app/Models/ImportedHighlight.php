<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportedHighlight extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'source',
        'book_title',
        'author',
        'quote',
        'note',
        'location',
        'highlighted_at',
    ];

    protected $casts = [
        'highlighted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
