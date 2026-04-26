<?php

namespace App\Services\Modules\Book;

use App\Interfaces\Modules\Book\BookRepositoryInterface;
use App\Interfaces\Modules\Book\BookServiceInterface;
use App\Models\Book;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class BookService implements BookServiceInterface
{
    protected BookRepositoryInterface $bookRepository;

    public function __construct(BookRepositoryInterface $bookRepository)
    {
        $this->bookRepository = $bookRepository;
    }

    /**
     * Get all books from the catalog.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Book>
     */
    public function getAllBooks(): Collection
    {
        return $this->bookRepository->getAll();
    }

    /**
     * Get a specific book by ID.
     *
     * @param  int  $id
     * @return \App\Models\Book|null
     */
    public function getBookById(int $id): ?Book
    {
        return $this->bookRepository->findById($id);
    }

    /**
     * Create a new book in the catalog.
     *
     * @param  array  $data
     * @return \App\Models\Book
     * @throws \Illuminate\Validation\ValidationException
     */
    public function createBook(array $data): Book
    {
        // Regra de negócio: Não permitir duplicação de livros (título + autor case insensitive)
        if ($this->bookRepository->findByTitleAndAuthor($data['title'], $data['author'])) {
            throw ValidationException::withMessages([
                'title' => 'Um livro com este título e autor já existe no catálogo.',
                'author' => 'Um livro com este título e autor já existe no catálogo.',
            ]);
        }

        return $this->bookRepository->create($data);
    }

    /**
     * Update an existing book in the catalog.
     *
     * @param  int  $id
     * @param  array  $data
     * @return \App\Models\Book
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updateBook(int $id, array $data): Book
    {
        // Regra de negócio: Verificar duplicação se título ou autor forem alterados
        if (isset($data['title']) || isset($data['author'])) {
            $existingBook = $this->bookRepository->findByTitleAndAuthor(
                $data['title'] ?? $this->bookRepository->findById($id)->title,
                $data['author'] ?? $this->bookRepository->findById($id)->author
            );

            if ($existingBook && $existingBook->id !== $id) {
                throw ValidationException::withMessages([
                    'title' => 'Outro livro com este título e autor já existe no catálogo.',
                    'author' => 'Outro livro com este título e autor já existe no catálogo.',
                ]);
            }
        }

        return $this->bookRepository->update($id, $data);
    }

    /**
     * Delete a book from the catalog.
     *
     * @param  int  $id
     * @return bool
     */
    public function deleteBook(int $id): bool
    {
        return $this->bookRepository->delete($id);
    }
}
