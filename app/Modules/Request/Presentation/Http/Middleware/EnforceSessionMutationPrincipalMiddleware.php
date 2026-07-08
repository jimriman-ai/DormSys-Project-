<?php

declare(strict_types=1);

namespace App\Modules\Request\Presentation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures mutation principal identity on Request HTTP endpoints is sourced
 * exclusively from the authenticated API session, never from pre-set attributes.
 *
 * When Livewire replays route middleware on a duplicated request, the resolved
 * principal is also mirrored onto the ambient request so mutation code can read it.
 */
final class EnforceSessionMutationPrincipalMiddleware
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('api');

        if ($user !== null) {
            $principalId = (string) $user->getAuthIdentifier();
            $request->attributes->set('audit_principal_user_id', $principalId);

            $ambientRequest = request();

            if ($ambientRequest !== $request) {
                $ambientRequest->attributes->set('audit_principal_user_id', $principalId);
            }
        }

        return $next($request);
    }
}
