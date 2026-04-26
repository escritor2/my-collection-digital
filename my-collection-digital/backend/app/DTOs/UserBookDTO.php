<?php

namespace App\DTOs;

use Spatie\LaravelData\Data;

class UserBookDTO extends Data
{
    public function __construct(
        public int $user_id,
        public int $book_id,
        public ?string $status,
        public ?int $progress_pages,
        public ?int $rating,
        public ?string $started_at,
        public ?string $finished_at,
    ) {}
}
