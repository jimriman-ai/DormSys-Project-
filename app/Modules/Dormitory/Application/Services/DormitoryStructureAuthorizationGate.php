<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\Services;

use App\Application\Mutation\Contracts\MutationPrincipalContextPort;
use App\Modules\Dormitory\Application\Exceptions\UnauthorizedDormitoryStructureAccessException;
use App\Modules\Identity\Application\Authorization\DormitoryStructurePermissionCatalog;
use App\Modules\Identity\Application\Contracts\IdentityUserReadContract;

/**
 * Application-layer PEP for approved Dormitory structure permissions.
 */
final class DormitoryStructureAuthorizationGate
{
    public function __construct(
        private readonly MutationPrincipalContextPort $principalContext,
        private readonly IdentityUserReadContract $identityRead,
    ) {}

    public function assertStructureView(): void
    {
        $this->assertPermission(DormitoryStructurePermissionCatalog::VIEW);
    }

    public function assertStructureManage(): void
    {
        $this->assertPermission(DormitoryStructurePermissionCatalog::MANAGE);
    }

    /**
     * Unresolved Phase 3C occupancy-marker APIs remain denied until Spec02 vocabulary resolves them.
     */
    public function assertUnresolvedActionDenied(): never
    {
        throw new UnauthorizedDormitoryStructureAccessException(
            'Dormitory action is denied by default until authorization vocabulary is resolved.'
        );
    }

    private function assertPermission(string $permissionName): void
    {
        $principalId = $this->principalContext->currentPrincipalId();

        if ($principalId === null || $principalId === '') {
            throw new UnauthorizedDormitoryStructureAccessException(
                'Dormitory structure access requires an authorized principal.'
            );
        }

        if (! $this->identityRead->isUserActive($principalId)) {
            throw new UnauthorizedDormitoryStructureAccessException(
                'Dormitory structure access requires an active identity user.'
            );
        }

        if (! $this->identityRead->userHasPermission($principalId, $permissionName)) {
            throw new UnauthorizedDormitoryStructureAccessException(
                'Dormitory structure permission is required.'
            );
        }
    }
}
