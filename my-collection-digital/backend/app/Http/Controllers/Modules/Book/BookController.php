<?php

namespace App\Http\Controllers\Modules\Book;

use App\Http\Controllers\Controller;
use App\Interfaces\Modules\Book\BookServiceInterface;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
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
     * @return JsonResponse
     */
    public function index()
    {
        $filters = request()->validate([
            'q' => ['nullable', 'string', 'max:200'],
        ]);

        $q = trim((string) ($filters['q'] ?? ''));

        $books = Book::query()
            ->when($q !== '', function ($query) use ($q) {
                $prefix = $q.'%';
                $like = '%'.$q.'%';
                $query->where(function ($inner) use ($q) {
                    $inner->where('title', 'like', '%'.$q.'%')
                        ->orWhere('author', 'like', '%'.$q.'%')
                        ->orWhere('isbn', 'like', '%'.$q.'%');
                })
                    ->select('*')
                    ->selectRaw(
                        '(CASE
                        WHEN title LIKE ? THEN 120
                        WHEN author LIKE ? THEN 90
                        WHEN isbn LIKE ? THEN 80
                        WHEN title LIKE ? THEN 60
                        WHEN author LIKE ? THEN 40
                        WHEN isbn LIKE ? THEN 30
                        ELSE 0
                    END) as relevance_score',
                        [$prefix, $prefix, $prefix, $like, $like, $like]
                    )
                    ->orderByDesc('relevance_score');
            })
            ->withExists(['userBooks as in_shelf' => fn ($ub) => $ub->where('user_id', Auth::id())])
            ->when($q === '', fn ($query) => $query->orderBy('title'))
            ->limit(200)
            ->get();

        $response = response()->json([
            'data' => $books,
        ]);
        
        // Cache público: 1 minuto para buscas com filtro, 5 minutos para lista geral
        $cacheSeconds = $q === '' ? 300 : 60;
        $response->header('Cache-Control', 'public, max-age='.$cacheSeconds);
        $response->header('Vary', 'Authorization');
        
        return $response;
    }

    /**
     * Show the form for creating a new book.
     *
     * @return JsonResponse
     */
    public function create()
    {
        // Removed as this is an API
        return response()->json(['message' => 'Not implemented'], 501);
    }

    /**
     * Store a newly created book in storage.
     *
     * @return JsonResponse
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
                'cover_url' => ['nullable', 'string', 'max:2048'],
                'language' => ['nullable', 'string', 'max:16'],
                'publisher' => ['nullable', 'string', 'max:255'],
                'published_date' => ['nullable', 'string', 'max:32'],
                'categories' => ['nullable', 'array'],
                'categories.*' => ['string', 'max:80'],
                'google_volume_id' => ['nullable', 'string', 'max:255'],
                'open_library_key' => ['nullable', 'string', 'max:255'],
            ]);

            $existing = $this->findPotentialDuplicate($validatedData);
            if ($existing) {
                $mergedPayload = $this->mergeBookData($existing, $validatedData);
                $book = $this->bookService->updateBook($existing->id, $mergedPayload);

                return response()->json([
                    'message' => 'Livro já existia no catálogo e foi enriquecido com novos metadados.',
                    'data' => $book,
                    'meta' => ['deduplicated' => true],
                ]);
            }

            $book = $this->bookService->createBook(array_merge($validatedData, ['created_by' => Auth::id()]));

            return response()->json([
                'message' => 'Livro adicionado ao catálogo com sucesso!',
                'data' => $book,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * Display the specified book.
     *
     * @return JsonResponse
     */
    public function show(int $id)
    {
        $book = Book::query()
            ->whereKey($id)
            ->withExists(['userBooks as in_shelf' => fn ($ub) => $ub->where('user_id', Auth::id())])
            ->first();

        if (! $book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        return response()->json([
            'data' => $book,
        ]);
    }

    /**
     * Show the form for editing the specified book.
     *
     * @return JsonResponse
     */
    public function edit(int $id)
    {
        // Removed as this is an API
        return response()->json(['message' => 'Not implemented'], 501);
    }

    /**
     * Update the specified book in storage.
     *
     * @return JsonResponse
     */
    public function update(Request $request, int $id)
    {
        try {
            $validatedData = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'author' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'isbn' => ['nullable', 'string', 'max:20', 'unique:books,isbn,'.$id], // Ignora o próprio ID na validação unique
                'page_count' => ['nullable', 'integer', 'min:1'],
                'cover_url' => ['nullable', 'string', 'max:2048'],
                'language' => ['nullable', 'string', 'max:16'],
                'publisher' => ['nullable', 'string', 'max:255'],
                'published_date' => ['nullable', 'string', 'max:32'],
                'categories' => ['nullable', 'array'],
                'categories.*' => ['string', 'max:80'],
                'google_volume_id' => ['nullable', 'string', 'max:255'],
                'open_library_key' => ['nullable', 'string', 'max:255'],
            ]);

            $book = $this->bookService->updateBook($id, $validatedData);

            return response()->json([
                'message' => 'Livro atualizado com sucesso!',
                'data' => $book,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * Remove the specified book from storage.
     *
     * @return JsonResponse
     */
    public function destroy(int $id)
    {
        $this->bookService->deleteBook($id);

        return response()->json(['message' => 'Livro removido do catálogo com sucesso!']);
    }

    private function findPotentialDuplicate(array $payload): ?Book
    {
        $isbn = trim((string) ($payload['isbn'] ?? ''));
        $googleId = trim((string) ($payload['google_volume_id'] ?? ''));
        $openKey = trim((string) ($payload['open_library_key'] ?? ''));
        $title = trim((string) ($payload['title'] ?? ''));
        $author = trim((string) ($payload['author'] ?? ''));

        if ($isbn !== '') {
            $found = Book::query()->where('isbn', $isbn)->first();
            if ($found) {
                return $found;
            }
        }

        if ($googleId !== '') {
            $found = Book::query()->where('google_volume_id', $googleId)->first();
            if ($found) {
                return $found;
            }
        }

        if ($openKey !== '') {
            $found = Book::query()->where('open_library_key', $openKey)->first();
            if ($found) {
                return $found;
            }
        }

        if ($title !== '' && $author !== '') {
            return Book::query()
                ->whereRaw('LOWER(TRIM(title)) = ?', [mb_strtolower($title)])
                ->whereRaw('LOWER(TRIM(author)) = ?', [mb_strtolower($author)])
                ->first();
        }

        return null;
    }

    private function mergeBookData(Book $existing, array $incoming): array
    {
        $merged = $existing->toArray();
        foreach ($incoming as $key => $value) {
            if ($key === 'categories') {
                $current = is_array($existing->categories) ? $existing->categories : [];
                $incomingCategories = is_array($value) ? $value : [];
                $merged[$key] = array_values(array_unique(array_filter(array_merge($current, $incomingCategories))));

                continue;
            }

            if ($value === null) {
                continue;
            }
            if (is_string($value) && trim($value) === '') {
                continue;
            }
            $merged[$key] = $value;
        }

        return $merged;
    }
}
