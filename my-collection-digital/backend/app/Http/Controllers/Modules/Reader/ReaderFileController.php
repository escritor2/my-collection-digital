<?php

namespace App\Http\Controllers\Modules\Reader;

use App\Http\Controllers\Controller;
use App\Models\UserBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReaderFileController extends Controller
{
    private function requireUserBook(int $userBookId): UserBook
    {
        return UserBook::where('id', $userBookId)
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }

    public function upload(Request $request, int $userBookId)
    {
        $userBook = $this->requireUserBook($userBookId);

        $validated = $request->validate([
            'file' => ['required', 'file', 'max:51200', 'mimetypes:application/pdf,application/epub+zip'],
        ]);

        $userBook->clearMediaCollection('source');

        $userBook
            ->addMedia($validated['file'])
            ->usingName($validated['file']->getClientOriginalName())
            ->toMediaCollection('source');

        return response()->json(['message' => 'File uploaded']);
    }

    public function download(int $userBookId)
    {
        $userBook = $this->requireUserBook($userBookId);
        $media = $userBook->getFirstMedia('source');

        abort_if(! $media, 404, 'File not found');

        return response()->file($media->getPath(), [
            'Content-Type' => $media->mime_type ?? 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.addslashes($media->file_name).'"',
        ]);
    }
}
