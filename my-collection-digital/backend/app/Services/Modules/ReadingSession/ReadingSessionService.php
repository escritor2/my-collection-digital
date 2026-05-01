<?php

namespace App\Services\Modules\ReadingSession;

use App\Interfaces\Modules\ReadingSession\ReadingSessionRepositoryInterface;
use App\Interfaces\Modules\ReadingSession\ReadingSessionServiceInterface;
use App\Interfaces\Modules\UserShelf\UserBookRepositoryInterface;
use App\Models\ReadingSession;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class ReadingSessionService implements ReadingSessionServiceInterface
{
    protected ReadingSessionRepositoryInterface $readingSessionRepository;

    protected UserBookRepositoryInterface $userBookRepository;

    public function __construct(
        ReadingSessionRepositoryInterface $readingSessionRepository,
        UserBookRepositoryInterface $userBookRepository
    ) {
        $this->readingSessionRepository = $readingSessionRepository;
        $this->userBookRepository = $userBookRepository;
    }

    /**
     * Get all reading sessions for a specific user.
     *
     * @return Collection<int, ReadingSession>
     */
    public function getUserReadingSessions(int $userId): Collection
    {
        return $this->readingSessionRepository->getAllUserReadingSessions($userId);
    }

    /**
     * Get a specific reading session by ID for a user.
     */
    public function getReadingSessionById(int $id, int $userId): ?ReadingSession
    {
        return $this->readingSessionRepository->findReadingSessionById($id, $userId);
    }

    /**
     * Start a new reading session.
     *
     * @throws ValidationException
     */
    public function startReadingSession(int $userId, int $userBookId, array $data): ReadingSession
    {
        $userBook = $this->userBookRepository->findUserBookById($userBookId, $userId);

        if (! $userBook) {
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
     * @throws ValidationException
     * @throws ModelNotFoundException
     */
    public function endReadingSession(int $readingSessionId, int $userId, array $data): ReadingSession
    {
        $session = $this->readingSessionRepository->findReadingSessionById($readingSessionId, $userId);

        if (! $session) {
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

        return $session;
    }

    /**
     * Delete a reading session.
     *
     * @throws ValidationException
     */
    public function deleteReadingSession(int $id, int $userId): bool
    {
        $session = $this->readingSessionRepository->findReadingSessionById($id, $userId);

        if (! $session) {
            throw ValidationException::withMessages([
                'reading_session_id' => 'Sessão de leitura não encontrada.',
            ]);
        }

        return $this->readingSessionRepository->delete($id);
    }

    /**
     * Get all reading sessions for a specific user book.
     *
     * @return Collection<int, ReadingSession>
     */
    public function getReadingSessionsByUserBook(int $userBookId, int $userId): Collection
    {
        return $this->readingSessionRepository->getUserBookReadingSessions($userBookId, $userId);
    }
}
