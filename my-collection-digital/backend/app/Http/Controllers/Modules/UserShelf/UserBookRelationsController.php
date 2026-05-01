<?php

namespace App\Http\Controllers\Modules\UserShelf;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Tag;
use App\Models\UserBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserBookRelationsController extends Controller
{
    public function syncTags(Request $request, UserBook $userBook)
    {
        if ($userBook->user_id !== Auth::id()) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $validated = $request->validate([
            'tag_ids' => ['required', 'array'],
            'tag_ids.*' => ['integer'],
        ]);

        $tagIds = Tag::query()
            ->where('user_id', Auth::id())
            ->whereIn('id', $validated['tag_ids'])
            ->pluck('id')
            ->all();

        $userBook->tags()->sync($tagIds);
        $userBook->load(['book', 'tags', 'collections']);

        return response()->json(['data' => $userBook]);
    }

    public function syncCollections(Request $request, UserBook $userBook)
    {
        if ($userBook->user_id !== Auth::id()) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $validated = $request->validate([
            'collection_ids' => ['required', 'array'],
            'collection_ids.*' => ['integer'],
        ]);

        $collectionIds = Collection::query()
            ->where('user_id', Auth::id())
            ->whereIn('id', $validated['collection_ids'])
            ->pluck('id')
            ->all();

        $userBook->collections()->sync($collectionIds);
        $userBook->load(['book', 'tags', 'collections']);

        return response()->json(['data' => $userBook]);
    }
}
