<?php

namespace Coolsam\Modules\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ModulePanelAuthMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (config('filament-modules.panels.require_auth', true) && ! auth()->check()) {
            return redirect(config('filament-modules.panels.back_to_main_url', '/admin'));
        }

        return $next($request);
    }
}
