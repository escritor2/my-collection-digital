<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'description',
        'isbn',
        'page_count',
        'created_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function userBooks(): HasMany
    {
        return $this->hasMany(UserBook::class);
    }

    public function scopeFindByTitleAndAuthor($query, string $title, string $author)
    {
        return $query->whereRaw('LOWER(title) = ?', [strtolower($title)])
                     ->whereRaw('LOWER(author) = ?', [strtolower($author)]);
    }
}
