<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PasswordUpdateRequest;
use App\Http\Requests\Settings\TwoFactorAuthenticationRequest;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Features;

class SecurityController extends Controller
{
    public function __construct()
    {
        if (
            Features::canManageTwoFactorAuthentication() &&
            Features::optionEnabled(
                Features::twoFactorAuthentication(),
                'confirmPassword'
            )
        ) {
            $this->middleware('password.confirm')->only('edit');
        }
    }

    /**
     * Show the user's security settings page.
     */
    public function edit(TwoFactorAuthenticationRequest $request)
    {
        $props = [
            'canManageTwoFactor' => Features::canManageTwoFactorAuthentication(),
        ];

        if (Features::canManageTwoFactorAuthentication()) {
            $request->ensureStateIsValid();

            $props['twoFactorEnabled'] = $request->user()->hasEnabledTwoFactorAuthentication();
            $props['requiresConfirmation'] = Features::optionEnabled(
                Features::twoFactorAuthentication(),
                'confirm'
            );
        }

        return response()->json([
            'data' => $props,
        ]);
    }

    /**
     * Update the user's password.
     */
    public function update(PasswordUpdateRequest $request): JsonResponse
    {
        $request->user()->update([
            'password' => $request->password,
        ]);

        return response()->json([
            'message' => 'Senha atualizada com sucesso.',
        ]);
    }
}