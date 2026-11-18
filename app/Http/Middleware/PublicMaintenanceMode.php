<?php

namespace App\Http\Middleware;

use App\Support\SiteSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PublicMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app(SiteSettings::class)->get('public_maintenance_mode', false)) {
            return $next($request);
        }

        // Keep the admin panel and its Livewire requests usable so an authorized
        // administrator can switch the public site back on.
        if ($request->is('admin', 'admin/*', 'livewire/*') || $request->user()?->can('settings.manage')) {
            return $next($request);
        }

        return response()
            ->view('pages.maintenance', status: Response::HTTP_SERVICE_UNAVAILABLE)
            ->header('Cache-Control', 'no-store, private');
    }
}
