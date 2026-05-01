<?php

namespace App\Repositories\Modules\Book;

use App\Interfaces\Modules\Book\BookRepositoryInterface;
use App\Models\Book;
use Illuminate\Database\Eloquent\Collection;

class BookRepository implements BookRepositoryInterface
{
    public function getAll(): Collection
    {
        return Book::all();
    }

    public function findById(int $id): ?Book
    {
        return Book::find($id);
    }

    public function create(array $data): Book
    {
        return Book::create($data);
    }

    public function update(int $id, array $data): Book
    {
        $book = Book::findOrFail($id);
        $book->update($data);

        return $book;
    }

    public function delete(int $id): bool
    {
        return Book::destroy($id) > 0;
    }

    public function findByTitleAndAuthor(string $title, string $author): ?Book
    {
        return Book::findByTitleAndAuthor($title, $author)->first();
    }
}
