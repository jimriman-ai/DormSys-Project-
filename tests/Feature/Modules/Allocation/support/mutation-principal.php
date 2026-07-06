<?php

declare(strict_types=1);

/**
 * @template TReturn
 *
 * @param  callable(): TReturn  $callback
 * @return TReturn
 */
function runAllocationMutation(callable $callback, ?string $actorId = null): mixed
{
    return withAllocationMutationActor($callback, $actorId);
}
