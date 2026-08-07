<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request)
    {
        return response()->json([
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return response()->json(['message' => 'Perfil atualizado com sucesso.']);
    }

    /**
     * Update the user's avatar (profile photo).
     */
    public function updateAvatar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'avatar' => ['required', 'image', 'max:4096', 'mimes:jpg,jpeg,png,webp'],
        ]);

        $user = $request->user();

        $user->clearMediaCollection('avatar');
        $user->addMedia($validated['avatar'])->toMediaCollection('avatar');

        return response()->json([
            'message' => 'Foto de perfil atualizada com sucesso.',
            'data' => ['avatar_url' => $user->fresh()->avatar_url],
        ]);
    }

    /**
     * Remove the user's avatar (profile photo).
     */
    public function destroyAvatar(Request $request): JsonResponse
    {
        $request->user()->clearMediaCollection('avatar');

        return response()->json([
            'message' => 'Foto de perfil removida.',
            'data' => ['avatar_url' => null],
        ]);
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(ProfileDeleteRequest $request): JsonResponse
    {
        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Conta excluída com sucesso.']);
    }
}
