<?php

namespace App\Interfaces\Modules\Book;

use App\Models\Book;
use Illuminate\Database\Eloquent\Collection;

interface BookRepositoryInterface
{
    public function getAll(): Collection;
    public function findById(int $id): ?Book;
    public function create(array $data): Book;
    public function update(int $id, array $data): Book;
    public function delete(int $id): bool;
    public function findByTitleAndAuthor(string $title, string $author): ?Book;
}
