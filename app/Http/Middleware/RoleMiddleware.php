<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $userRole = auth()->user()?->role?->role_name;

        if (!$userRole || !in_array($userRole, $roles)) {
            return redirect()->route('dashboard')
                ->with('error', 'Unauthorized access.');
        }

        return $next($request);
    }
}
