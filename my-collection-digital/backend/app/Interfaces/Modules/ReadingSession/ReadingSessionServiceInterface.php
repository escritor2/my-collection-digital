<?php

namespace App\Interfaces\Modules\ReadingSession;

use App\Models\ReadingSession;
use Illuminate\Database\Eloquent\Collection;

interface ReadingSessionServiceInterface
{
    public function getUserReadingSessions(int $userId): Collection;

    public function getReadingSessionById(int $id, int $userId): ?ReadingSession;

    public function startReadingSession(int $userId, int $userBookId, array $data): ReadingSession;

    public function endReadingSession(int $readingSessionId, int $userId, array $data): ReadingSession;

    public function deleteReadingSession(int $id, int $userId): bool;

    public function getReadingSessionsByUserBook(int $userBookId, int $userId): Collection;
}
