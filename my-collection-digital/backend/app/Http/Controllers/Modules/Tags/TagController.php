<?php

namespace App\Http\Controllers\Modules\Tags;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TagController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Tag::query()
                ->where('user_id', Auth::id())
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'color' => ['nullable', 'string', 'max:32'],
        ]);

        $tag = Tag::query()->firstOrCreate(
            ['user_id' => Auth::id(), 'name' => $validated['name']],
            ['color' => $validated['color'] ?? null]
        );

        return response()->json(['data' => $tag], 201);
    }

    public function destroy(Tag $tag)
    {
        if ($tag->user_id !== Auth::id()) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $tag->delete();

        return response()->json(['message' => 'Tag removida']);
    }
}
