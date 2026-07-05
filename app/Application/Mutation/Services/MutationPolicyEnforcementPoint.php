<?php

declare(strict_types=1);

namespace App\Application\Mutation\Services;

use App\Application\Mutation\Contracts\MutationAuthorizationPort;
use App\Application\Mutation\Contracts\MutationPrincipalContextPort;
use App\Application\Mutation\Exceptions\UnauthorizedMutationException;
use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Registry\SystemMutationCapabilityRegistry;
use App\Shared\ValueObjects\SystemActorId;

final class MutationPolicyEnforcementPoint
{
    public function __construct(
        private readonly MutationPrincipalContextPort $principalContext,
        private readonly MutationAuthorizationPort $authorization,
    ) {}

    /**
     * @param  array<string, mixed>  $context
     */
    public function enforce(string $capabilityKey, array $context = []): void
    {
        MutationCapabilityCatalog::assertValidKey($capabilityKey);

        $principalId = $this->principalContext->currentPrincipalId();

        if (SystemMutationCapabilityRegistry::isRegistered($capabilityKey)) {
            if ($principalId !== SystemActorId::VALUE) {
                throw new UnauthorizedMutationException('System mutation capability requires the system actor.');
            }

            return;
        }

        MutationCapabilityCatalog::assertRegistered($capabilityKey);

        if ($principalId === null || $principalId === '') {
            throw new UnauthorizedMutationException('Mutation requires an authorized principal.');
        }

        $this->authorization->authorize($capabilityKey, $principalId, $context);
    }
}
