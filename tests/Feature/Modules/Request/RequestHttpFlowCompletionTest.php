<?php

declare(strict_types=1);

use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Modules\Request\Application\Contracts\RequestApprovalRepositoryContract;
use App\Modules\Request\Application\Contracts\RequestRepositoryContract;
use App\Modules\Request\Domain\States\ApprovedState;
use App\Modules\Request\Domain\States\DraftState;
use App\Modules\Request\Domain\States\PendingDepartmentManagerState;
use App\Modules\Request\Domain\States\PendingDormitoryManagerState;
use App\Modules\Request\Domain\States\PendingDormitoryUnitState;
use App\Modules\Request\Domain\States\PendingHRState;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Carbon::setTestNow('2026-06-23 12:00:00');
    app(MutationPrincipalContextHolder::class)->clear();
    request()->attributes->remove('audit_principal_user_id');
});

afterEach(function (): void {
    Carbon::setTestNow();
});

describe('http create and read', function (): void {
    it('creates a draft personal request for the authenticated employee', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateRequestHttpMutationUser($actor['identity']);

        $dormitoryId = createDormitorySiteForRequestTests();

        $this->postJson('/api/requests/personal', [
            'dormitoryId' => $dormitoryId,
            'checkInDate' => '2026-07-01',
            'checkOutDate' => '2026-12-31',
        ])->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', DraftState::$name)
            ->assertJsonPath('data.employeeId', $actor['employee']->requireId()->value)
            ->assertJsonPath('data.dormitoryId', $dormitoryId)
            ->assertJsonPath('data.type', 'personal');
    });

    it('rejects create when principal has no linked employee', function (): void {
        $identity = createIdentityUserThroughMutation(
            'Unlinked Request User',
            'unlinked.request.'.uniqid('', true).'@example.com',
        );

        authenticateRequestHttpMutationUser(
            UserModel::query()->findOrFail($identity->requireId()->value),
        );

        $this->postJson('/api/requests/personal', [
            'dormitoryId' => createDormitorySiteForRequestTests(),
            'checkInDate' => '2026-07-01',
            'checkOutDate' => '2026-12-31',
        ])->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Authenticated principal has no linked employee.');
    });

    it('shows an owned request and lists it under mine', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateRequestHttpMutationUser($actor['identity']);

        $created = $this->postJson('/api/requests/personal', [
            'dormitoryId' => createDormitorySiteForRequestTests(),
            'checkInDate' => '2026-07-01',
            'checkOutDate' => '2026-12-31',
        ])->assertCreated()
            ->json('data');

        $this->getJson('/api/requests/'.$created['id'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $created['id'])
            ->assertJsonPath('data.status', DraftState::$name);

        $this->getJson('/api/requests/mine')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $created['id']);
    });

    it('denies show when authenticated principal does not own the request', function (): void {
        $owner = createRequestHttpMutationEmployee();
        $other = createRequestHttpMutationEmployee('0000000019');
        $draft = createDraftPersonalRequestForHttp($owner['employee']);

        authenticateRequestHttpMutationUser($other['identity']);

        $this->getJson('/api/requests/'.$draft->requireId()->value)
            ->assertForbidden()
            ->assertJsonPath('message', 'Mutation actor must own the request.');
    });
});

describe('http end-to-end lifecycle', function (): void {
    it('runs create submit and four-stage approval to approved', function (): void {
        $owner = createRequestHttpMutationEmployee();
        authenticateRequestHttpMutationUser($owner['identity']);

        $created = $this->postJson('/api/requests/personal', [
            'dormitoryId' => createDormitorySiteForRequestTests(),
            'checkInDate' => '2026-07-01',
            'checkOutDate' => '2026-12-31',
        ])->assertCreated()
            ->json('data');

        $submitted = $this->postJson(requestHttpMutationUrl($created['id'], 'submit'))
            ->assertOk()
            ->assertJsonPath('data.status', PendingDepartmentManagerState::$name)
            ->json('data');

        $stage1ApproverId = app(RequestRepositoryContract::class)
            ->findById(RequestId::fromString($submitted['id']))
            ?->assignedStage1ApproverIdentityId;

        expect($stage1ApproverId)->not->toBeNull()->not->toBe('');

        $expectedStatuses = [
            PendingHRState::$name,
            PendingDormitoryManagerState::$name,
            PendingDormitoryUnitState::$name,
            ApprovedState::$name,
        ];

        $currentId = $submitted['id'];

        foreach ($expectedStatuses as $index => $expectedStatus) {
            if ($index === 0) {
                authenticateRequestHttpMutationUser(
                    UserModel::query()->findOrFail($stage1ApproverId),
                );
            } else {
                $approver = createMutationApprover();
                authenticateRequestHttpMutationUser(
                    UserModel::query()->findOrFail($approver['principalId']),
                );
            }

            $currentId = $this->postJson(requestHttpMutationUrl($currentId, 'approve'))
                ->assertOk()
                ->assertJsonPath('data.status', $expectedStatus)
                ->json('data.id');
        }

        expect(app(RequestApprovalRepositoryContract::class)->countForRequest(
            RequestId::fromString($currentId),
        ))->toBe(4);
    });
});

describe('http domain failures', function (): void {
    it('returns not found for unknown request id on show', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateRequestHttpMutationUser($actor['identity']);

        $this->getJson('/api/requests/'.UuidGenerator::uuid7())
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Request not found.');
    });

    it('returns conflict when submitting an already submitted request', function (): void {
        $actor = createRequestHttpMutationEmployee();
        $draft = createDraftPersonalRequestForHttp($actor['employee']);

        authenticateRequestHttpMutationUser($actor['identity']);

        $this->postJson(requestHttpMutationUrl($draft->requireId()->value, 'submit'))
            ->assertOk();

        $this->postJson(requestHttpMutationUrl($draft->requireId()->value, 'submit'))
            ->assertConflict()
            ->assertJsonPath('message', 'Only draft requests can be submitted.');
    });
});
