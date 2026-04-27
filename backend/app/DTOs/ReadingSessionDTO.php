<?php

namespace App\DTOs;

use Spatie\LaravelData\Data;

class ReadingSessionDTO extends Data
{
    public function __construct(
        public int $user_id,
        public int $user_book_id,
        public string $started_at,
        public ?string $ended_at,
        public ?int $duration_minutes,
        public ?int $pages_read,
    ) {}
}
