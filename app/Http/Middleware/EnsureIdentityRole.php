<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Auth\IdentityRoleGuard;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces Spatie roles with guard_name = identity (SEC-G-01).
 */
final class EnsureIdentityRole
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = auth('identity')->user();

        if ($user === null) {
            abort(403);
        }

        if (! IdentityRoleGuard::userHasIdentityRole($user, ...$roles)) {
            abort(403);
        }

        return $next($request);
    }
}
