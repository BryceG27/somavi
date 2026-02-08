<?php

namespace App\Http\Middleware;

use App\Models\UserGroup;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->userGroup?->slug !== UserGroup::ADMIN_SLUG) {
            abort(403, 'Accesso non autorizzato');
        }

        return $next($request);
    }
}
