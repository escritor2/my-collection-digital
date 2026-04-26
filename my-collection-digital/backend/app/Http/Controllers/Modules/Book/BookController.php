<?php

namespace App\Http\Controllers\Modules\Book;

use App\Http\Controllers\Controller;
use App\Interfaces\Modules\Book\BookServiceInterface;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class BookController extends Controller
{
    protected BookServiceInterface $bookService;

    public function __construct(BookServiceInterface $bookService)
    {
        $this->bookService = $bookService;
    }

    /**
     * Display a listing of the books.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $books = $this->bookService->getAllBooks();
        return response()->json([
            'data' => $books,
        ]);
    }

    /**
     * Show the form for creating a new book.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        // Removed as this is an API
        return response()->json(['message' => 'Not implemented'], 501);
    }

    /**
     * Store a newly created book in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'author' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'isbn' => ['nullable', 'string', 'max:20', 'unique:books'],
                'page_count' => ['nullable', 'integer', 'min:1'],
            ]);

            $book = $this->bookService->createBook(array_merge($validatedData, ['created_by' => Auth::id()]));

            return response()->json([
                'message' => 'Livro adicionado ao catálogo com sucesso!',
                'data' => $book
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * Display the specified book.
     *
     * @param  int  $id
     * @return \Inertia\Response
     */
    public function show(int $id)
    {
        $book = $this->bookService->getBookById($id);

        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        return response()->json([
            'data' => $book,
        ]);
    }

    /**
     * Show the form for editing the specified book.
     *
     * @param  int  $id
     * @return \Inertia\Response
     */
    public function edit(int $id)
    {
        // Removed as this is an API
        return response()->json(['message' => 'Not implemented'], 501);
    }

    /**
     * Update the specified book in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, int $id)
    {
        try {
            $validatedData = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'author' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'isbn' => ['nullable', 'string', 'max:20', 'unique:books,isbn,' . $id], // Ignora o próprio ID na validação unique
                'page_count' => ['nullable', 'integer', 'min:1'],
            ]);

            $book = $this->bookService->updateBook($id, $validatedData);

            return response()->json([
                'message' => 'Livro atualizado com sucesso!',
                'data' => $book
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * Remove the specified book from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(int $id)
    {
        $this->bookService->deleteBook($id);
        return response()->json(['message' => 'Livro removido do catálogo com sucesso!']);
    }
}
