<?php

declare(strict_types=1);

use App\Modules\Workflow\Application\Contracts\RequestApprovalWorkflowRepositoryContract;
use App\Modules\Workflow\Domain\Entities\RequestApprovalWorkflowInstance;
use App\Modules\Workflow\Domain\Enums\RequestApprovalWorkflowStage;
use App\Modules\Workflow\Domain\Enums\WorkflowInstanceStatus;
use App\Modules\Workflow\Domain\Enums\WorkflowStepStatus;
use App\Modules\Workflow\Domain\ValueObjects\IdentityUserId;
use App\Modules\Workflow\Domain\ValueObjects\RequestReferenceId;
use App\Modules\Workflow\Infrastructure\Repositories\EloquentRequestApprovalWorkflowRepository;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('binds the eloquent repository for the workflow persistence contract', function (): void {
    expect(app(RequestApprovalWorkflowRepositoryContract::class))
        ->toBeInstanceOf(EloquentRequestApprovalWorkflowRepository::class);
});

it('saves and finds a request approval workflow instance with steps', function (): void {
    /** @var RequestApprovalWorkflowRepositoryContract $repo */
    $repo = app(RequestApprovalWorkflowRepositoryContract::class);

    $now = new DateTimeImmutable('2026-07-21 10:00:00', new DateTimeZone('UTC'));
    $requestId = RequestReferenceId::fromString('11111111-1111-7111-8111-111111111111');
    $actor = IdentityUserId::fromString('22222222-2222-7222-8222-222222222222');

    $instance = RequestApprovalWorkflowInstance::start($requestId, $actor, $now);
    $repo->save($instance);

    $loaded = $repo->findById($instance->id);
    expect($loaded)->not->toBeNull()
        ->and($loaded->id->value)->toBe($instance->id->value)
        ->and($loaded->status)->toBe(WorkflowInstanceStatus::Running)
        ->and($loaded->currentStage)->toBe(RequestApprovalWorkflowStage::DepartmentManager)
        ->and($loaded->steps)->toHaveCount(1)
        ->and($loaded->steps[0]->status)->toBe(WorkflowStepStatus::Pending);

    $running = $repo->findRunningByRequestId($requestId);
    expect($running)->not->toBeNull()
        ->and($running->id->value)->toBe($instance->id->value);

    $running->approve($actor, $now->modify('+1 hour'));
    $repo->save($running);

    $after = $repo->findById($instance->id);
    expect($after)->not->toBeNull()
        ->and($after->currentStage)->toBe(RequestApprovalWorkflowStage::HR)
        ->and($after->steps)->toHaveCount(2)
        ->and($after->steps[0]->status)->toBe(WorkflowStepStatus::Approved)
        ->and($after->steps[1]->status)->toBe(WorkflowStepStatus::Pending);
});

it('enforces one running instance per request via partial unique index', function (): void {
    /** @var RequestApprovalWorkflowRepositoryContract $repo */
    $repo = app(RequestApprovalWorkflowRepositoryContract::class);

    $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
    $requestId = RequestReferenceId::fromString('33333333-3333-7333-8333-333333333333');
    $actor = IdentityUserId::fromString('44444444-4444-7444-8444-444444444444');

    $repo->save(RequestApprovalWorkflowInstance::start($requestId, $actor, $now));

    expect(fn () => $repo->save(RequestApprovalWorkflowInstance::start($requestId, $actor, $now)))
        ->toThrow(QueryException::class);
});

it('does not define a foreign key from request_id to requests', function (): void {
    $exists = DB::selectOne(
        <<<'SQL'
        SELECT to_regclass('public.workflow_request_approval_instances') AS rel
        SQL
    );
    expect($exists?->rel)->not->toBeNull();

    $fks = DB::select(
        <<<'SQL'
        SELECT conname
        FROM pg_constraint
        WHERE conrelid = 'workflow_request_approval_instances'::regclass
          AND contype = 'f'
        SQL
    );

    expect($fks)->toBe([]);
});
