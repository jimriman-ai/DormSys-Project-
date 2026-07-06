<?php

declare(strict_types=1);

use App\Application\Mutation\Support\MutationPrincipalContext;

/**
 * @template TReturn
 *
 * @param  callable(): TReturn  $callback
 * @return TReturn
 */
function asCheckInMutationPrincipal(string $principalId, callable $callback): mixed
{
    return MutationPrincipalContext::runAs($principalId, $callback);
}

/**
 * @template TReturn
 *
 * @param  callable(): TReturn  $callback
 * @return TReturn
 */
function asCheckInOperator(string $operatorId, callable $callback): mixed
{
    return asCheckInMutationPrincipal($operatorId, $callback);
}
