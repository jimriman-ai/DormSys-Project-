<?php

declare(strict_types=1);

use App\Application\Mutation\Support\MutationPrincipalContext;
use App\Modules\Employee\Domain\Entities\Employee;
use App\Modules\Request\Application\Services\ApproveRequestStageAction;
use App\Modules\Request\Application\Services\RejectRequestAction;
use App\Modules\Request\Domain\Entities\Request;
use App\Modules\Request\Domain\ValueObjects\ApproverReferenceId;

/**
 * @template TReturn
 *
 * @param  callable(): TReturn  $callback
 * @return TReturn
 */
function asRequestMutationPrincipal(string $principalId, callable $callback): mixed
{
    grantDormitoryStructureViewPermission($principalId);

    return MutationPrincipalContext::runAs($principalId, $callback);
}

/**
 * @template TReturn
 *
 * @param  callable(): TReturn  $callback
 * @return TReturn
 */
function asRequestOwner(Employee $employee, callable $callback): mixed
{
    return asRequestMutationPrincipal($employee->identityId->value, $callback);
}

/**
 * @return array{principalId: string, approverId: ApproverReferenceId}
 */
function createMutationApprover(): array
{
    $user = createIdentityUserThroughMutation(
        'Mutation Approver',
        'approver.'.uniqid('', true).'@example.com',
    );
    $principalId = $user->requireId()->value;
    grantDormitoryStructureViewPermission($principalId);

    return [
        'principalId' => $principalId,
        'approverId' => ApproverReferenceId::fromString($principalId),
    ];
}

/**
 * @param  array{principalId: string, approverId: ApproverReferenceId}|null  $approver
 *
 * @template TReturn
 *
 * @param  callable(ApproverReferenceId): TReturn  $callback
 * @return TReturn
 */
function asMutationApprover(callable $callback, ?array $approver = null): mixed
{
    $approver ??= createMutationApprover();

    return asRequestMutationPrincipal($approver['principalId'], fn () => $callback($approver['approverId']));
}

/**
 * @param  array{principalId: string, approverId: ApproverReferenceId}|null  $approver
 */
function approveRequestStageForTest(Request $request, ?array $approver = null): Request
{
    return asMutationApprover(
        fn (ApproverReferenceId $approverId) => app(ApproveRequestStageAction::class)->execute($request->requireId(), $approverId),
        $approver,
    );
}

/**
 * @param  array{principalId: string, approverId: ApproverReferenceId}|null  $approver
 */
function rejectRequestStageForTest(Request $request, string $reason, ?array $approver = null): Request
{
    return asMutationApprover(
        fn (ApproverReferenceId $approverId) => app(RejectRequestAction::class)->execute($request->requireId(), $approverId, $reason),
        $approver,
    );
}
