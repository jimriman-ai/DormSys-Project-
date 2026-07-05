<?php

declare(strict_types=1);

namespace App\Application\Mutation\Contracts;

interface MutationAuthorizationPort
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function authorize(string $capabilityKey, string $principalId, array $context = []): void;
}
