<?php

namespace App\Services\Modules\ReadingSession;

use App\Interfaces\Modules\ReadingSession\ReadingSessionRepositoryInterface;
use App\Interfaces\Modules\ReadingSession\ReadingSessionServiceInterface;
use App\Interfaces\Modules\UserShelf\UserBookRepositoryInterface;
use App\Interfaces\Modules\ReadingStreak\ReadingStreakServiceInterface;
use App\Models\ReadingSession;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class ReadingSessionService implements ReadingSessionServiceInterface
{
    protected ReadingSessionRepositoryInterface $readingSessionRepository;
    protected UserBookRepositoryInterface $userBookRepository;
    protected ReadingStreakServiceInterface $streakService;

    public function __construct(
        ReadingSessionRepositoryInterface $readingSessionRepository,
        UserBookRepositoryInterface $userBookRepository,
        ReadingStreakServiceInterface $streakService
    )
    {
        $this->readingSessionRepository = $readingSessionRepository;
        $this->userBookRepository = $userBookRepository;
        $this->streakService = $streakService;
    }

    /**
     * Get all reading sessions for a specific user.
     *
     * @param  int  $userId
     * @return \Illuminate\Database\Eloquent\Collection<int, ReadingSession>
     */
    public function getUserReadingSessions(int $userId): Collection
    {
        return $this->readingSessionRepository->getAllUserReadingSessions($userId);
    }

    /**
     * Get a specific reading session by ID for a user.
     *
     * @param  int  $id
     * @param  int  $userId
     * @return \App\Models\ReadingSession|null
     */
    public function getReadingSessionById(int $id, int $userId): ?ReadingSession
    {
        return $this->readingSessionRepository->findReadingSessionById($id, $userId);
    }

    /**
     * Start a new reading session.
     *
     * @param  int  $userId
     * @param  int  $userBookId
     * @param  array  $data
     * @return \App\Models\ReadingSession
     * @throws \Illuminate\Validation\ValidationException
     */
    public function startReadingSession(int $userId, int $userBookId, array $data): ReadingSession
    {
        $userBook = $this->userBookRepository->findUserBookById($userBookId, $userId);

        if (!$userBook) {
            throw ValidationException::withMessages([
                'user_book_id' => 'Livro não encontrado na sua estante para iniciar a sessão.',
            ]);
        }

        // Regra de negócio: Um livro não pode ser 'lido' para iniciar uma sessão
        if ($userBook->status === 'lido') {
            throw ValidationException::withMessages([
                'user_book_id' => 'Não é possível iniciar uma sessão de leitura para um livro já lido.',
            ]);
        }

        // Atualiza o status do livro para 'lendo' se ainda não estiver
        if ($userBook->status === 'quero_ler') {
            $userBook->update(['status' => 'lendo', 'started_at' => now()]);
        }

        $data['user_id'] = $userId;
        $data['user_book_id'] = $userBookId;
        $data['started_at'] = Carbon::parse($data['started_at'] ?? now());
        // 'ended_at', 'duration_minutes', 'pages_read' serão definidos ao finalizar a sessão

        return $this->readingSessionRepository->create($data);
    }

    /**
     * End an existing reading session.
     *
     * @param  int  $readingSessionId
     * @param  int  $userId
     * @param  array  $data
     * @return \App\Models\ReadingSession
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function endReadingSession(int $readingSessionId, int $userId, array $data): ReadingSession
    {
        $session = $this->readingSessionRepository->findReadingSessionById($readingSessionId, $userId);

        if (!$session) {
            throw ValidationException::withMessages([
                'reading_session_id' => 'Sessão de leitura não encontrada.',
            ]);
        }

        $endedAt = Carbon::parse($data['ended_at'] ?? now());

        if ($endedAt->lessThan($session->started_at)) {
            throw ValidationException::withMessages([
                'ended_at' => 'A data de término não pode ser anterior à data de início da sessão.',
            ]);
        }

        $durationMinutes = $endedAt->diffInMinutes($session->started_at);

        $session = $this->readingSessionRepository->update($readingSessionId, [
            'ended_at' => $endedAt,
            'duration_minutes' => $durationMinutes,
            'pages_read' => $data['pages_read'],
        ]);

        // Atualizar o progresso do livro na estante do usuário
        $userBook = $session->userBook;
        if ($userBook) {
            $userBook->progress_pages += $data['pages_read'];
            // O mutator setProgressPagesAttribute no modelo UserBook cuidará da atualização do status para 'lido'
            $userBook->save();
        }

        // Record streak check-in
        $this->streakService->recordCheckIn($userId);

        return $session;
    }

    /**
     * Delete a reading session.
     *
     * @param  int  $id
     * @param  int  $userId
     * @return bool
     * @throws \Illuminate\Validation\ValidationException
     */
    public function deleteReadingSession(int $id, int $userId): bool
    {
        $session = $this->readingSessionRepository->findReadingSessionById($id, $userId);

        if (!$session) {
            throw ValidationException::withMessages([
                'reading_session_id' => 'Sessão de leitura não encontrada.',
            ]);
        }

        return $this->readingSessionRepository->delete($id);
    }

    /**
     * Get all reading sessions for a specific user book.
     *
     * @param  int  $userBookId
     * @param  int  $userId
     * @return \Illuminate\Database\Eloquent\Collection<int, ReadingSession>
     */
    public function getReadingSessionsByUserBook(int $userBookId, int $userId): Collection
    {
        return $this->readingSessionRepository->getUserBookReadingSessions($userBookId, $userId);
    }
}
