<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PortalPasswordController extends Controller
{
    public function edit(Request $request): View
    {
        return view('portal.auth.password-change', [
            'user' => $request->user('portal'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $portalUser = $request->user('portal');

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if (! Hash::check($validated['current_password'], $portalUser->password)) {
            return back()->withErrors([
                'current_password' => 'La contraseña actual no coincide.',
            ]);
        }

        $portalUser->forceFill([
            'password' => $validated['password'],
            'must_change_password' => false,
            'password_changed_at' => now(),
        ])->save();

        return redirect()
            ->route('portal.dashboard')
            ->with('status', 'Contraseña actualizada correctamente.');
    }
}
