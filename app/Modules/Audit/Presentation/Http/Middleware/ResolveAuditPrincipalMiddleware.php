<?php

declare(strict_types=1);

namespace App\Modules\Audit\Presentation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ResolveAuditPrincipalMiddleware
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->attributes->has('audit_principal_user_id')) {
            $user = $request->user();

            if ($user !== null) {
                $request->attributes->set('audit_principal_user_id', (string) $user->getAuthIdentifier());
            }
        }

        $principalId = $request->attributes->get('audit_principal_user_id');

        if (! is_string($principalId) || $principalId === '') {
            abort(401, 'Unauthenticated');
        }

        return $next($request);
    }
}
