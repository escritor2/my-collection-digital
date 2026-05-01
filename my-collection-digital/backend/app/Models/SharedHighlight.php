<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SharedHighlight extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reader_annotation_id',
        'is_public',
        'share_token',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function annotation(): BelongsTo
    {
        return $this->belongsTo(ReaderAnnotation::class, 'reader_annotation_id');
    }
}
