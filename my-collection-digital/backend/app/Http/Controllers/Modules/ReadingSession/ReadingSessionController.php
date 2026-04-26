<?php

namespace App\Http\Controllers\Modules\ReadingSession;

use App\Http\Controllers\Controller;
use App\Interfaces\Modules\ReadingSession\ReadingSessionServiceInterface;
use App\Interfaces\Modules\UserShelf\UserBookServiceInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ReadingSessionController extends Controller
{
    protected ReadingSessionServiceInterface $readingSessionService;
    protected UserBookServiceInterface $userBookService;

    public function __construct(
        ReadingSessionServiceInterface $readingSessionService,
        UserBookServiceInterface $userBookService
    )
    {
        $this->readingSessionService = $readingSessionService;
        $this->userBookService = $userBookService;
    }

    /**
     * Display a listing of the user's reading sessions.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $readingSessions = $this->readingSessionService->getUserReadingSessions(Auth::id());
        return Inertia::render('ReadingSessions/Index', [
            'readingSessions' => $readingSessions,
        ]);
    }

    /**
     * Show the form for creating a new reading session.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        $userBooks = $this->userBookService->getUserShelf(Auth::id());
        return Inertia::render('ReadingSessions/Create', [
            'userBooks' => $userBooks,
        ]);
    }

    /**
     * Store a newly created reading session in storage (start session).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
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

            return redirect()->route('reading-sessions.index')->with('success', 'Sessão de leitura iniciada com sucesso!');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Display the specified reading session.
     *
     * @param  int  $id
     * @return \Inertia\Response
     */
    public function show(int $id)
    {
        $session = $this->readingSessionService->getReadingSessionById($id, Auth::id());

        if (!$session) {
            abort(404);
        }

        return Inertia::render('ReadingSessions/Show', [
            'readingSession' => $session,
        ]);
    }

    /**
     * Update the specified reading session in storage (end session).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, int $id)
    {
        try {
            $validatedData = $request->validate([
                'ended_at' => ['required', 'date'],
                'pages_read' => ['required', 'integer', 'min:1'],
            ]);

            $session = $this->readingSessionService->endReadingSession($id, Auth::id(), $validatedData);

            return redirect()->route('reading-sessions.index')->with('success', 'Sessão de leitura finalizada e progresso atualizado!');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Remove the specified reading session from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(int $id)
    {
        $this->readingSessionService->deleteReadingSession($id, Auth::id());
        return redirect()->route('reading-sessions.index')->with('success', 'Sessão de leitura removida com sucesso!');
    }
}
