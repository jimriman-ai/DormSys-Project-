<?php

declare(strict_types=1);

use App\Modules\Request\Application\Contracts\Stage1ApproverIdentityReadContract;

/**
 * [PERMIT-ID: IMPL-PERMIT-02] Feature-test stub: satisfy FK + fail-closed create path.
 *
 * Production resolves via IdentityRoleGuard → dormitory-manager (identity guard).
 */
function bindStage1ApproverIdentityFixtureForTests(): string
{
    $user = createIdentityUserThroughMutation(
        'Stage1 Approver Fixture',
        'stage1.fixture.'.uniqid('', true).'@example.com',
    );
    $identityId = $user->requireId()->value;

    app()->instance(
        Stage1ApproverIdentityReadContract::class,
        new class($identityId) implements Stage1ApproverIdentityReadContract
        {
            public function __construct(private readonly string $identityId) {}

            public function resolveActiveDormitoryManagerIdentityId(): ?string
            {
                return $this->identityId !== '' ? $this->identityId : null;
            }
        },
    );

    return $identityId;
}
