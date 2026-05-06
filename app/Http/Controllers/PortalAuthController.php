<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortalAuthController extends Controller
{
    public function create(): View
    {
        return view('portal.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('portal')->attempt($credentials, remember: true)) {
            return back()->withErrors([
                'email' => 'Credenciales inválidas para el portal.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        $portalUser = Auth::guard('portal')->user();
        $redirectRoute = $portalUser?->must_change_password
            ? 'portal.password.edit'
            : 'portal.dashboard';

        return redirect()->intended(route($redirectRoute));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('portal')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login');
    }
}
