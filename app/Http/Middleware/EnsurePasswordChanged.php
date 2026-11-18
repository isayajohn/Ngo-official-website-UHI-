<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()?->must_change_password && ! $request->routeIs('filament.admin.auth.profile', 'filament.admin.auth.logout', 'livewire.*')) {
            return redirect()->route('filament.admin.auth.profile');
        }

        return $next($request);
    }
}
