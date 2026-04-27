<?php

namespace App\Services\Modules\UserShelf;

use App\Interfaces\Modules\Book\BookRepositoryInterface;
use App\Interfaces\Modules\UserShelf\UserBookRepositoryInterface;
use App\Interfaces\Modules\UserShelf\UserBookServiceInterface;
use App\Models\UserBook;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class UserBookService implements UserBookServiceInterface
{
    protected UserBookRepositoryInterface $userBookRepository;
    protected BookRepositoryInterface $bookRepository;

    public function __construct(
        UserBookRepositoryInterface $userBookRepository,
        BookRepositoryInterface $bookRepository
    )
    {
        $this->userBookRepository = $userBookRepository;
        $this->bookRepository = $bookRepository;
    }

    /**
     * Get all books on a user's shelf.
     *
     * @param  int  $userId
     * @return \Illuminate\Database\Eloquent\Collection<int, UserBook>
     */
    public function getUserShelf(int $userId): Collection
    {
        return $this->userBookRepository->getAllUserBooks($userId);
    }

    /**
     * Add a book to a user's shelf.
     *
     * @param  int  $userId
     * @param  int  $bookId
     * @param  array  $data
     * @return \App\Models\UserBook
     * @throws \Illuminate\Validation\ValidationException
     */
    public function addBookToShelf(int $userId, int $bookId, array $data = []): UserBook
    {
        // Verificar se o livro existe no catálogo
        if (!$this->bookRepository->findById($bookId)) {
            throw ValidationException::withMessages([
                'book_id' => 'Livro não encontrado no catálogo.',
            ]);
        }

        // Verificar se o livro já está na estante do usuário
        if ($this->userBookRepository->findUserBookByBookId($bookId, $userId)) {
            throw ValidationException::withMessages([
                'book_id' => 'Este livro já está na sua estante.',
            ]);
        }

        $data['user_id'] = $userId;
        $data['book_id'] = $bookId;
        $data['status'] = $data['status'] ?? 'quero_ler'; // Status padrão

        return $this->userBookRepository->create($data);
    }

    /**
     * Update a book on a user's shelf.
     *
     * @param  int  $userBookId
     * @param  int  $userId
     * @param  array  $data
     * @return \App\Models\UserBook
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function updateUserBook(int $userBookId, int $userId, array $data): UserBook
    {
        $userBook = $this->userBookRepository->findUserBookById($userBookId, $userId);

        if (!$userBook) {
            throw ValidationException::withMessages([
                'user_book_id' => 'Livro não encontrado na sua estante.',
            ]);
        }

        // A lógica de atualização de status para 'lido' já está no mutator do modelo UserBook
        return $this->userBookRepository->update($userBookId, $data);
    }

    /**
     * Remove a book from a user's shelf.
     *
     * @param  int  $userBookId
     * @param  int  $userId
     * @return bool
     * @throws \Illuminate\Validation\ValidationException
     */
    public function removeBookFromShelf(int $userBookId, int $userId): bool
    {
        $userBook = $this->userBookRepository->findUserBookById($userBookId, $userId);

        if (!$userBook) {
            throw ValidationException::withMessages([
                'user_book_id' => 'Livro não encontrado na sua estante.',
            ]);
        }

        return $this->userBookRepository->delete($userBookId);
    }

    /**
     * Get details of a specific user book.
     *
     * @param  int  $userBookId
     * @param  int  $userId
     * @return \App\Models\UserBook|null
     */
    public function getUserBookDetails(int $userBookId, int $userId): ?UserBook
    {
        return $this->userBookRepository->findUserBookById($userBookId, $userId);
    }
}
