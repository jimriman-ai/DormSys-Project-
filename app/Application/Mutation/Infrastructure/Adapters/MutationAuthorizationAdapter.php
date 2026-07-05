<?php

declare(strict_types=1);

namespace App\Application\Mutation\Infrastructure\Adapters;

use App\Application\Mutation\Contracts\MutationAuthorizationPort;
use App\Application\Mutation\Exceptions\UnauthorizedMutationException;

final class MutationAuthorizationAdapter implements MutationAuthorizationPort
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function authorize(string $capabilityKey, string $principalId, array $context = []): void
    {
        if ($principalId === '') {
            throw new UnauthorizedMutationException('Mutation requires an authorized principal.');
        }
    }
}
