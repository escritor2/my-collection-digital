<?php

namespace App\Repositories\Modules\ReadingSession;

use App\Interfaces\Modules\ReadingSession\ReadingSessionRepositoryInterface;
use App\Models\ReadingSession;
use Illuminate\Database\Eloquent\Collection;

class ReadingSessionRepository implements ReadingSessionRepositoryInterface
{
    /**
     * Get all reading sessions for a specific user.
     *
     * @param  int  $userId
     * @return \Illuminate\Database\Eloquent\Collection<int, ReadingSession>
     */
    public function getAllUserReadingSessions(int $userId): Collection
    {
        return ReadingSession::where('user_id', $userId)->with('userBook.book')->get();
    }

    /**
     * Find a reading session by its ID for a specific user.
     *
     * @param  int  $id
     * @param  int  $userId
     * @return \App\Models\ReadingSession|null
     */
    public function findReadingSessionById(int $id, int $userId): ?ReadingSession
    {
        return ReadingSession::where('id', $id)->where('user_id', $userId)->with('userBook.book')->first();
    }

    /**
     * Create a new reading session.
     *
     * @param  array  $data
     * @return \App\Models\ReadingSession
     */
    public function create(array $data): ReadingSession
    {
        return ReadingSession::create($data);
    }

    /**
     * Update an existing reading session.
     *
     * @param  int  $id
     * @param  array  $data
     * @return \App\Models\ReadingSession
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update(int $id, array $data): ReadingSession
    {
        $readingSession = ReadingSession::findOrFail($id);
        $readingSession->update($data);
        return $readingSession;
    }

    /**
     * Delete a reading session.
     *
     * @param  int  $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return ReadingSession::destroy($id) > 0;
    }

    /**
     * Get all reading sessions for a specific user book.
     *
     * @param  int  $userBookId
     * @param  int  $userId
     * @return \Illuminate\Database\Eloquent\Collection<int, ReadingSession>
     */
    public function getUserBookReadingSessions(int $userBookId, int $userId): Collection
    {
        return ReadingSession::where('user_book_id', $userBookId)
                             ->where('user_id', $userId)
                             ->get();
    }
}
