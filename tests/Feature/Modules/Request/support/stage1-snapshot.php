<?php

declare(strict_types=1);

use App\Modules\Request\Application\Contracts\Stage1ApproverIdentityReadContract;

/**
 * [PERMIT-ID: IMPL-PERMIT-02] Feature-test stub: satisfy FK + fail-closed create path
 * without requiring every fixture to wire full org-chart.
 *
 * Production uses Stage1ApproverIdentityReadBridge (department.manager_id → identity).
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

            public function resolveForEmployee(string $employeeId): ?string
            {
                return $this->identityId;
            }
        },
    );

    return $identityId;
}
