<?php

namespace App\Http\Controllers\Modules\Collections;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CollectionController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Collection::query()
                ->where('user_id', Auth::id())
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $collection = Collection::query()->firstOrCreate(
            ['user_id' => Auth::id(), 'name' => $validated['name']],
            ['description' => $validated['description'] ?? null]
        );

        return response()->json(['data' => $collection], 201);
    }

    public function destroy(Collection $collection)
    {
        if ($collection->user_id !== Auth::id()) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $collection->delete();

        return response()->json(['message' => 'Coleção removida']);
    }
}
