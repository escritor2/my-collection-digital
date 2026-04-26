<?php

namespace App\Http\Controllers\Modules\UserShelf;

use App\Http\Controllers\Controller;
use App\Interfaces\Modules\UserShelf\UserBookServiceInterface;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UserBookController extends Controller
{
    protected UserBookServiceInterface $userBookService;

    public function __construct(UserBookServiceInterface $userBookService)
    {
        $this->userBookService = $userBookService;
    }

    /**
     * Display a listing of the user's books (shelf).
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $userBooks = $this->userBookService->getUserShelf(Auth::id());
        return response()->json([
            'data' => $userBooks,
        ]);
    }

    /**
     * Store a newly created user book in storage (add to shelf).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'book_id' => ['required', 'integer', 'exists:books,id'],
                'status' => ['nullable', 'string', 'in:quero_ler,lendo,lido,abandonei'],
            ]);

            $userBook = $this->userBookService->addBookToShelf(Auth::id(), $validatedData['book_id'], $validatedData);

            return response()->json([
                'message' => 'Livro adicionado à sua estante com sucesso!',
                'data' => $userBook
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * Display the specified user book.
     *
     * @param  int  $id
     * @return \Inertia\Response
     */
    public function show(int $id)
    {
        $userBook = $this->userBookService->getUserBookDetails($id, Auth::id());

        if (!$userBook) {
            return response()->json(['message' => 'Livro não encontrado na estante'], 404);
        }

        return response()->json([
            'data' => $userBook,
        ]);
    }

    /**
     * Update the specified user book in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, int $id)
    {
        try {
            $validatedData = $request->validate([
                'status' => ['required', 'string', 'in:quero_ler,lendo,lido,abandonei'],
                'progress_pages' => ['nullable', 'integer', 'min:0'],
                'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
                'started_at' => ['nullable', 'date'],
                'finished_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            ]);

            $userBook = $this->userBookService->updateUserBook($id, Auth::id(), $validatedData);

            return response()->json([
                'message' => 'Livro na estante atualizado com sucesso!',
                'data' => $userBook
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * Remove the specified user book from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(int $id)
    {
        $this->userBookService->removeBookFromShelf($id, Auth::id());
        return response()->json(['message' => 'Livro removido da sua estante com sucesso!']);
    }
}
