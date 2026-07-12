<?php

declare(strict_types=1);

namespace App\Modules\Audit\Presentation\Http\Middleware;

use App\Modules\Audit\Application\Contracts\AuditAuthorizationPort;
use App\Modules\Audit\Domain\Exceptions\UnauthorizedAuditAccessException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureAuditHistoryReadMiddleware
{
    public function __construct(
        private readonly AuditAuthorizationPort $authorization,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $this->authorization->authorizeRead();
        } catch (UnauthorizedAuditAccessException $exception) {
            abort(Response::HTTP_FORBIDDEN, $exception->getMessage());
        }

        return $next($request);
    }
}
