<?php

namespace App\Interfaces\Modules\ReadingSession;

use App\Models\ReadingSession;
use Illuminate\Database\Eloquent\Collection;

interface ReadingSessionRepositoryInterface
{
    public function getAllUserReadingSessions(int $userId): Collection;

    public function findReadingSessionById(int $id, int $userId): ?ReadingSession;

    public function create(array $data): ReadingSession;

    public function update(int $id, array $data): ReadingSession;

    public function delete(int $id): bool;

    public function getUserBookReadingSessions(int $userBookId, int $userId): Collection;
}
