<?php

namespace App\Http\Controllers\Modules\Social;

use App\Http\Controllers\Controller;
use App\Models\BookClub;
use App\Models\BookClubMembership;
use App\Models\BookClubPost;
use App\Models\BookReview;
use App\Models\CuratedList;
use App\Models\CuratedListItem;
use App\Models\ReaderAnnotation;
use App\Models\SharedHighlight;
use App\Models\User;
use App\Models\UserFollow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SocialController extends Controller
{
    public function feed()
    {
        $userId = Auth::id();
        $followingIds = UserFollow::query()
            ->where('follower_id', $userId)
            ->pluck('followed_id')
            ->all();
        $scopeUserIds = array_values(array_unique(array_merge([$userId], $followingIds)));

        $reviewItems = BookReview::query()
            ->whereIn('user_id', $scopeUserIds)
            ->where('is_public', true)
            ->with(['user:id,name,email', 'book:id,title,author'])
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn ($r) => [
                'type' => 'review',
                'created_at' => $r->created_at,
                'data' => $r,
            ]);

        $highlightItems = SharedHighlight::query()
            ->whereIn('user_id', $scopeUserIds)
            ->where('is_public', true)
            ->with(['user:id,name,email', 'annotation.userBook.book'])
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn ($h) => [
                'type' => 'highlight',
                'created_at' => $h->created_at,
                'data' => $h,
            ]);

        $listItems = CuratedList::query()
            ->whereIn('user_id', $scopeUserIds)
            ->where('is_public', true)
            ->with(['user:id,name,email', 'items.book'])
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn ($l) => [
                'type' => 'list',
                'created_at' => $l->created_at,
                'data' => $l,
            ]);

        $clubPostItems = BookClubPost::query()
            ->whereHas('club.memberships', fn ($m) => $m->where('user_id', $userId))
            ->with(['user:id,name,email', 'club:id,name'])
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn ($p) => [
                'type' => 'club_post',
                'created_at' => $p->created_at,
                'data' => $p,
            ]);

        $feed = $reviewItems
            ->concat($highlightItems)
            ->concat($listItems)
            ->concat($clubPostItems)
            ->sortByDesc('created_at')
            ->values()
            ->take(50)
            ->values();

        return response()->json(['data' => $feed]);
    }

    public function profile(User $user)
    {
        $viewerId = Auth::id();
        $isFollowing = UserFollow::query()
            ->where('follower_id', $viewerId)
            ->where('followed_id', $user->id)
            ->exists();

        return response()->json([
            'data' => [
                'user' => $user->only(['id', 'name', 'email', 'created_at']),
                'followers_count' => UserFollow::query()->where('followed_id', $user->id)->count(),
                'following_count' => UserFollow::query()->where('follower_id', $user->id)->count(),
                'is_following' => $isFollowing,
                'reviews' => BookReview::query()->where('user_id', $user->id)->where('is_public', true)->with('book')->latest()->limit(20)->get(),
                'lists' => CuratedList::query()->where('user_id', $user->id)->where('is_public', true)->with('items.book')->latest()->limit(20)->get(),
            ],
        ]);
    }

    public function follow(User $user)
    {
        if ($user->id === Auth::id()) {
            return response()->json(['message' => 'Cannot follow yourself'], 422);
        }

        UserFollow::query()->firstOrCreate([
            'follower_id' => Auth::id(),
            'followed_id' => $user->id,
        ]);

        return response()->json(['data' => ['following' => true]]);
    }

    public function unfollow(User $user)
    {
        UserFollow::query()
            ->where('follower_id', Auth::id())
            ->where('followed_id', $user->id)
            ->delete();

        return response()->json(['data' => ['following' => false]]);
    }

    public function createReview(Request $request)
    {
        $validated = $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string', 'min:2', 'max:4000'],
            'is_public' => ['nullable', 'boolean'],
        ]);

        $review = BookReview::query()->create([
            'user_id' => Auth::id(),
            ...$validated,
            'is_public' => (bool) ($validated['is_public'] ?? true),
        ]);

        return response()->json(['data' => $review->load(['book', 'user'])], 201);
    }

    public function listPublicReviews(Request $request)
    {
        $bookId = $request->query('book_id');
        $q = BookReview::query()->where('is_public', true)->with(['user:id,name,email', 'book:id,title,author']);
        if ($bookId) {
            $q->where('book_id', (int) $bookId);
        }

        return response()->json(['data' => $q->latest()->limit(100)->get()]);
    }

    public function createList(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_public' => ['nullable', 'boolean'],
        ]);

        $list = CuratedList::query()->create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_public' => (bool) ($validated['is_public'] ?? true),
        ]);

        return response()->json(['data' => $list], 201);
    }

    public function listMyLists()
    {
        return response()->json([
            'data' => CuratedList::query()
                ->where('user_id', Auth::id())
                ->with('items.book')
                ->latest()
                ->get(),
        ]);
    }

    public function addListItem(Request $request, CuratedList $list)
    {
        if ($list->user_id !== Auth::id()) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $validated = $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
        ]);

        $position = (int) CuratedListItem::query()
            ->where('curated_list_id', $list->id)
            ->max('position') + 1;

        CuratedListItem::query()->firstOrCreate(
            ['curated_list_id' => $list->id, 'book_id' => (int) $validated['book_id']],
            ['position' => $position]
        );

        return response()->json(['data' => $list->fresh()->load('items.book')]);
    }

    public function removeListItem(CuratedList $list, CuratedListItem $item)
    {
        if ($list->user_id !== Auth::id() || $item->curated_list_id !== $list->id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $item->delete();

        return response()->json(['data' => ['removed' => true]]);
    }

    public function shareHighlight(ReaderAnnotation $annotation)
    {
        if ($annotation->user_id !== Auth::id()) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $shared = SharedHighlight::query()->firstOrCreate(
            ['reader_annotation_id' => $annotation->id],
            [
                'user_id' => Auth::id(),
                'is_public' => true,
                'share_token' => Str::random(32),
            ]
        );

        return response()->json([
            'data' => $shared->load(['annotation.userBook.book', 'user']),
        ]);
    }

    public function createClub(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_public' => ['nullable', 'boolean'],
        ]);

        $club = BookClub::query()->create([
            'owner_id' => Auth::id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_public' => (bool) ($validated['is_public'] ?? true),
        ]);

        BookClubMembership::query()->firstOrCreate([
            'book_club_id' => $club->id,
            'user_id' => Auth::id(),
        ], [
            'role' => 'owner',
        ]);

        return response()->json(['data' => $club->load('memberships.user')], 201);
    }

    public function listClubs()
    {
        return response()->json([
            'data' => BookClub::query()
                ->where(function ($q) {
                    $q->where('is_public', true)
                        ->orWhereHas('memberships', fn ($m) => $m->where('user_id', Auth::id()));
                })
                ->withCount('memberships')
                ->latest()
                ->get(),
        ]);
    }

    public function joinClub(BookClub $club)
    {
        if (! $club->is_public) {
            return response()->json(['message' => 'Club is private'], 403);
        }

        BookClubMembership::query()->firstOrCreate([
            'book_club_id' => $club->id,
            'user_id' => Auth::id(),
        ], [
            'role' => 'member',
        ]);

        return response()->json(['data' => ['joined' => true]]);
    }

    public function leaveClub(BookClub $club)
    {
        BookClubMembership::query()
            ->where('book_club_id', $club->id)
            ->where('user_id', Auth::id())
            ->where('role', '!=', 'owner')
            ->delete();

        return response()->json(['data' => ['left' => true]]);
    }

    public function createClubPost(Request $request, BookClub $club)
    {
        $isMember = BookClubMembership::query()
            ->where('book_club_id', $club->id)
            ->where('user_id', Auth::id())
            ->exists();

        if (! $isMember) {
            return response()->json(['message' => 'Only members can post'], 403);
        }

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string', 'min:2', 'max:5000'],
        ]);

        $post = BookClubPost::query()->create([
            'book_club_id' => $club->id,
            'user_id' => Auth::id(),
            'title' => $validated['title'] ?? null,
            'content' => $validated['content'],
        ]);

        return response()->json(['data' => $post->load('user')], 201);
    }

    public function listClubPosts(BookClub $club)
    {
        $isMember = BookClubMembership::query()
            ->where('book_club_id', $club->id)
            ->where('user_id', Auth::id())
            ->exists();

        if (! $club->is_public && ! $isMember) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json([
            'data' => BookClubPost::query()
                ->where('book_club_id', $club->id)
                ->with('user')
                ->latest()
                ->limit(100)
                ->get(),
        ]);
    }
}
