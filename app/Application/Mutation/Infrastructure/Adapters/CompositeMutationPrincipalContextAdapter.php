<?php

declare(strict_types=1);

namespace App\Application\Mutation\Infrastructure\Adapters;

use App\Application\Mutation\Contracts\MutationPrincipalContextPort;
use App\Application\Mutation\Support\MutationPrincipalContextHolder;

final class CompositeMutationPrincipalContextAdapter implements MutationPrincipalContextPort
{
    public function __construct(
        private readonly MutationPrincipalContextHolder $holder,
    ) {}

    public function currentPrincipalId(): ?string
    {
        $fromHolder = $this->holder->get();

        if (is_string($fromHolder) && $fromHolder !== '') {
            return $fromHolder;
        }

        $fromRequest = request()->attributes->get('audit_principal_user_id');

        if (is_string($fromRequest) && $fromRequest !== '') {
            return $fromRequest;
        }

        $fromEnvironment = getenv('MUTATION_ACTING_PRINCIPAL');

        if (is_string($fromEnvironment) && $fromEnvironment !== '') {
            return $fromEnvironment;
        }

        return null;
    }
}
