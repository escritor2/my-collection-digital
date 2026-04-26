<?php

namespace App\Repositories\Modules\UserShelf;

use App\Interfaces\Modules\UserShelf\UserBookRepositoryInterface;
use App\Models\UserBook;
use Illuminate\Database\Eloquent\Collection;

class UserBookRepository implements UserBookRepositoryInterface
{
    /**
     * Get all user books for a specific user.
     *
     * @param  int  $userId
     * @return \Illuminate\Database\Eloquent\Collection<int, UserBook>
     */
    public function getAllUserBooks(int $userId): Collection
    {
        return UserBook::where('user_id', $userId)->with('book')->get();
    }

    /**
     * Find a user book by its ID for a specific user.
     *
     * @param  int  $id
     * @param  int  $userId
     * @return \App\Models\UserBook|null
     */
    public function findUserBookById(int $id, int $userId): ?UserBook
    {
        return UserBook::where('id', $id)->where('user_id', $userId)->with('book')->first();
    }

    /**
     * Find a user book by book ID for a specific user.
     *
     * @param  int  $bookId
     * @param  int  $userId
     * @return \App\Models\UserBook|null
     */
    public function findUserBookByBookId(int $bookId, int $userId): ?UserBook
    {
        return UserBook::where('book_id', $bookId)->where('user_id', $userId)->first();
    }

    /**
     * Create a new user book entry.
     *
     * @param  array  $data
     * @return \App\Models\UserBook
     */
    public function create(array $data): UserBook
    {
        return UserBook::create($data);
    }

    /**
     * Update an existing user book entry.
     *
     * @param  int  $id
     * @param  array  $data
     * @return \App\Models\UserBook
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update(int $id, array $data): UserBook
    {
        $userBook = UserBook::findOrFail($id);
        $userBook->update($data);
        return $userBook;
    }

    /**
     * Delete a user book entry.
     *
     * @param  int  $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return UserBook::destroy($id) > 0;
    }
}
