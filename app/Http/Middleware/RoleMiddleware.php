<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if (! in_array($user->role, $roles)) {
            if ($user->isAdmin() || $user->isKasir()) {
                return Redirect::route('admin.dashboard');
            }

            return Redirect::route('member.dashboard');
        }

        return $next($request);
    }
}
