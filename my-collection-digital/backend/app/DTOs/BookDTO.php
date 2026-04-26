<?php

namespace App\DTOs;

use Spatie\LaravelData\Data;

class BookDTO extends Data
{
    public function __construct(
        public string $title,
        public string $author,
        public ?string $description,
        public ?string $isbn,
        public ?int $page_count,
        public int $created_by,
    ) {}
}
