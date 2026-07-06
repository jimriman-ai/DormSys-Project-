<?php

declare(strict_types=1);

namespace App\Application\Mutation\Support;

use App\Shared\ValueObjects\SystemActorId;

final class MutationPrincipalContext
{
    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public static function runAsSystem(callable $callback): mixed
    {
        return self::runAs(SystemActorId::VALUE, $callback);
    }

    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public static function runAs(string $principalId, callable $callback): mixed
    {
        /** @var MutationPrincipalContextHolder $holder */
        $holder = app(MutationPrincipalContextHolder::class);
        $previous = $holder->get();
        $holder->set($principalId);

        $request = request();
        $hadRequestPrincipal = $request->attributes->has('audit_principal_user_id');
        $previousRequestPrincipal = $request->attributes->get('audit_principal_user_id');
        $request->attributes->set('audit_principal_user_id', $principalId);

        try {
            return $callback();
        } finally {
            $holder->set($previous);

            if ($hadRequestPrincipal) {
                $request->attributes->set('audit_principal_user_id', $previousRequestPrincipal);
            } else {
                $request->attributes->remove('audit_principal_user_id');
            }
        }
    }

    /**
     * Establish an isolated system-actor scope for approved non-HTTP runtime entrypoints.
     *
     * Clears any stale holder state before scoping so a prior leaked principal
     * cannot be restored when the callback completes.
     *
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public static function runJobAsSystem(callable $callback): mixed
    {
        app(MutationPrincipalContextHolder::class)->clear();
        request()->attributes->remove('audit_principal_user_id');

        return self::runAsSystem($callback);
    }
}
