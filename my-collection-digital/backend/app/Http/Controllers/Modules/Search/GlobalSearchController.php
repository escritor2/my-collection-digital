<?php

namespace App\Http\Controllers\Modules\Search;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\UserBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GlobalSearchController extends Controller
{
    private function relevanceCase(string $tablePrefix = ''): string
    {
        $t = $tablePrefix !== '' ? rtrim($tablePrefix, '.').'.' : '';

        return "(CASE
            WHEN {$t}title LIKE ? THEN 120
            WHEN {$t}author LIKE ? THEN 90
            WHEN {$t}isbn LIKE ? THEN 80
            WHEN {$t}title LIKE ? THEN 60
            WHEN {$t}author LIKE ? THEN 40
            WHEN {$t}isbn LIKE ? THEN 30
            ELSE 0
        END)";
    }

    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:1', 'max:200'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $q = trim($validated['q']);
        $limit = $validated['limit'] ?? 8;
        $prefix = $q.'%';
        $like = '%'.$q.'%';
        $bindings = [$prefix, $prefix, $prefix, $like, $like, $like];

        $books = Book::query()
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', '%'.$q.'%')
                    ->orWhere('author', 'like', '%'.$q.'%')
                    ->orWhere('isbn', 'like', '%'.$q.'%');
            })
            ->select('*')
            ->selectRaw($this->relevanceCase(), $bindings)
            ->orderByDesc('relevance_score')
            ->limit($limit)
            ->get();

        $shelf = UserBook::query()
            ->where('user_id', Auth::id())
            ->whereHas('book', function ($b) use ($q) {
                $b->where('title', 'like', '%'.$q.'%')
                    ->orWhere('author', 'like', '%'.$q.'%')
                    ->orWhere('isbn', 'like', '%'.$q.'%');
            })
            ->with('book')
            ->select('user_books.*')
            ->selectRaw($this->relevanceCase('books'), $bindings)
            ->join('books', 'books.id', '=', 'user_books.book_id')
            ->orderByDesc('relevance_score')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => [
                'books' => $books,
                'shelf' => $shelf,
            ],
        ]);
    }
}
