<?php

namespace App\Repositories\Modules\UserShelf;

use App\Interfaces\Modules\UserShelf\UserBookRepositoryInterface;
use App\Models\UserBook;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserBookRepository implements UserBookRepositoryInterface
{
    /**
     * Get all user books for a specific user.
     *
     * @return Collection<int, UserBook>
     */
    public function getAllUserBooks(int $userId, array $filters = []): Collection
    {
        $q = UserBook::query()
            ->where('user_id', $userId)
            ->with(['book', 'tags', 'collections']);

        if (! empty($filters['status'])) {
            $q->where('status', $filters['status']);
        }

        if (! empty($filters['q'])) {
            $search = trim($filters['q']);
            $q->whereHas('book', function ($b) use ($search) {
                $b->where('title', 'like', '%'.$search.'%')
                    ->orWhere('author', 'like', '%'.$search.'%')
                    ->orWhere('isbn', 'like', '%'.$search.'%');
            });
        }

        if (! empty($filters['tag_id'])) {
            $q->whereHas('tags', fn ($t) => $t->where('tags.id', (int) $filters['tag_id']));
        }

        if (! empty($filters['collection_id'])) {
            $q->whereHas('collections', fn ($c) => $c->where('collections.id', (int) $filters['collection_id']));
        }

        return $q->get();
    }

    /**
     * Find a user book by its ID for a specific user.
     */
    public function findUserBookById(int $id, int $userId): ?UserBook
    {
        return UserBook::where('id', $id)->where('user_id', $userId)->with('book')->first();
    }

    /**
     * Find a user book by book ID for a specific user.
     */
    public function findUserBookByBookId(int $bookId, int $userId): ?UserBook
    {
        return UserBook::where('book_id', $bookId)->where('user_id', $userId)->first();
    }

    /**
     * Create a new user book entry.
     */
    public function create(array $data): UserBook
    {
        return UserBook::create($data);
    }

    /**
     * Update an existing user book entry.
     *
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): UserBook
    {
        $userBook = UserBook::findOrFail($id);
        $userBook->update($data);

        return $userBook;
    }

    /**
     * Delete a user book entry.
     */
    public function delete(int $id): bool
    {
        return UserBook::destroy($id) > 0;
    }
}
