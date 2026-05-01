<?php

namespace App\Http\Controllers\Modules\Catalog;

use App\Http\Controllers\Controller;
use App\Services\Modules\Catalog\ExternalCatalogService;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function __construct(protected ExternalCatalogService $externalCatalogService) {}

    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:1', 'max:200'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        return response()->json(
            $this->externalCatalogService->search($validated['q'], $validated['limit'] ?? 8)
        );
    }

    public function resolveByIsbn(Request $request)
    {
        $validated = $request->validate([
            'isbn' => ['required', 'string', 'min:6', 'max:32'],
        ]);

        $data = $this->externalCatalogService->resolveByIsbn($validated['isbn']);

        if (! $data) {
            return response()->json(['data' => null], 404);
        }

        return response()->json(['data' => $data]);
    }
}
