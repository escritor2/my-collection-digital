<?php

namespace App\Http\Controllers\Modules\ReadingSession;

use App\Http\Controllers\Controller;
use App\Interfaces\Modules\ReadingSession\ReadingSessionServiceInterface;
use App\Interfaces\Modules\UserShelf\UserBookServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ReadingSessionController extends Controller
{
    protected ReadingSessionServiceInterface $readingSessionService;

    protected UserBookServiceInterface $userBookService;

    public function __construct(
        ReadingSessionServiceInterface $readingSessionService,
        UserBookServiceInterface $userBookService
    ) {
        $this->readingSessionService = $readingSessionService;
        $this->userBookService = $userBookService;
    }

    /**
     * Display a listing of the user's reading sessions.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $readingSessions = $this->readingSessionService->getUserReadingSessions(Auth::id());

        return response()->json([
            'data' => $readingSessions,
        ]);
    }

    /**
     * Show the form for creating a new reading session.
     *
     * @return JsonResponse
     */
    public function create()
    {
        // Forms are handled by the frontend
        return response()->json(['message' => 'Not implemented'], 501);
    }

    /**
     * Store a newly created reading session in storage (start session).
     *
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'user_book_id' => ['required', 'integer', 'exists:user_books,id'],
                'started_at' => ['nullable', 'date'],
            ]);

            $session = $this->readingSessionService->startReadingSession(
                Auth::id(),
                $validatedData['user_book_id'],
                $validatedData
            );

            return response()->json([
                'message' => 'Sessão de leitura iniciada com sucesso!',
                'data' => $session,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * Display the specified reading session.
     *
     * @return JsonResponse
     */
    public function show(int $id)
    {
        $session = $this->readingSessionService->getReadingSessionById($id, Auth::id());

        if (! $session) {
            return response()->json(['message' => 'Sessão não encontrada'], 404);
        }

        return response()->json([
            'data' => $session,
        ]);
    }

    /**
     * Update the specified reading session in storage (end session).
     *
     * @return JsonResponse
     */
    public function update(Request $request, int $id)
    {
        try {
            $validatedData = $request->validate([
                'ended_at' => ['required', 'date'],
                'pages_read' => ['required', 'integer', 'min:1'],
            ]);

            $session = $this->readingSessionService->endReadingSession($id, Auth::id(), $validatedData);

            return response()->json([
                'message' => 'Sessão de leitura finalizada e progresso atualizado!',
                'data' => $session,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * Remove the specified reading session from storage.
     *
     * @return JsonResponse
     */
    public function destroy(int $id)
    {
        $this->readingSessionService->deleteReadingSession($id, Auth::id());

        return response()->json([
            'message' => 'Sessão de leitura removida com sucesso!',
        ]);
    }
}
