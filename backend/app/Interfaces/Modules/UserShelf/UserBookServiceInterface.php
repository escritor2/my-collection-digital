<?php

namespace App\Interfaces\Modules\UserShelf;

use App\Models\UserBook;
use Illuminate\Database\Eloquent\Collection;

interface UserBookServiceInterface
{
    public function getUserShelf(int $userId): Collection;
    public function addBookToShelf(int $userId, int $bookId, array $data = []): UserBook;
    public function updateUserBook(int $userBookId, int $userId, array $data): UserBook;
    public function removeBookFromShelf(int $userBookId, int $userId): bool;
    public function getUserBookDetails(int $userBookId, int $userId): ?UserBook;
}
