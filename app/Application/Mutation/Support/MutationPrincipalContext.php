<?php

declare(strict_types=1);

namespace App\Application\Mutation\Support;

final class MutationPrincipalContext
{
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

        try {
            return $callback();
        } finally {
            $holder->set($previous);
        }
    }
}
