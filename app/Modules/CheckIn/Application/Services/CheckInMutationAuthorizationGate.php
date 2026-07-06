<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Application\Services;

use App\Application\Mutation\Contracts\MutationPrincipalContextPort;
use App\Application\Mutation\Exceptions\UnauthorizedMutationException;

final class CheckInMutationAuthorizationGate
{
    public function __construct(
        private readonly MutationPrincipalContextPort $principalContext,
    ) {}

    public function assertCreate(string $operatorId): void
    {
        $this->assertOperatorMatchesActor($operatorId);
    }

    public function assertOperate(string $operatorId): void
    {
        $this->assertOperatorMatchesActor($operatorId);
    }

    public function assertClose(string $operatorId): void
    {
        $this->assertOperatorMatchesActor($operatorId);
    }

    private function assertOperatorMatchesActor(string $operatorId): void
    {
        $principalId = $this->requirePrincipalId();

        if ($principalId !== $operatorId) {
            throw new UnauthorizedMutationException('Operator must match the mutation actor.');
        }
    }

    private function requirePrincipalId(): string
    {
        $principalId = $this->principalContext->currentPrincipalId();

        if ($principalId === null || $principalId === '') {
            throw new UnauthorizedMutationException('Mutation requires an authorized principal.');
        }

        return $principalId;
    }
}
