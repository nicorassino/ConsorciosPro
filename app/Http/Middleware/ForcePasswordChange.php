<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $portalUser = $request->user('portal');

        if (! $portalUser) {
            return redirect()->route('portal.login');
        }

        if ($portalUser->must_change_password && ! $request->routeIs('portal.password.*')) {
            return redirect()->route('portal.password.edit');
        }

        return $next($request);
    }
}
