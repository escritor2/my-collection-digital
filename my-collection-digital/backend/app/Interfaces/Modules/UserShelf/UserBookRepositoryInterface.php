<?php

namespace App\Interfaces\Modules\UserShelf;

use App\Models\UserBook;
use Illuminate\Database\Eloquent\Collection;

interface UserBookRepositoryInterface
{
    public function getAllUserBooks(int $userId, array $filters = []): Collection;

    public function findUserBookById(int $id, int $userId): ?UserBook;

    public function findUserBookByBookId(int $bookId, int $userId): ?UserBook;

    public function create(array $data): UserBook;

    public function update(int $id, array $data): UserBook;

    public function delete(int $id): bool;
}
