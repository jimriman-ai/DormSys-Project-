<?php

declare(strict_types=1);

use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\Allocation\Application\Contracts\AllocationReadContract;
use App\Modules\Allocation\Domain\Enums\AllocationMethod;
use App\Modules\Allocation\Domain\Enums\AllocationStatus;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

require_once __DIR__.'/RequestDrivenAllocationTest.php';

beforeEach(function (): void {
    Carbon::setTestNow('2026-07-01 12:00:00');
    app(MutationPrincipalContextHolder::class)->clear();
    request()->attributes->remove('audit_principal_user_id');
});

afterEach(function (): void {
    Carbon::setTestNow();
});

describe('http manual allocation flow', function (): void {
    it('creates shows and releases an allocation via http', function (): void {
        $operator = createAllocationHttpOperator();
        authenticateAllocationHttpUser($operator['identity']);

        $personId = UuidGenerator::uuid7();
        $bedId = createAssignableBedForAllocationTests();

        $created = $this->postJson(allocationHttpUrl(), [
            'personId' => $personId,
            'bedId' => $bedId,
            'startDate' => '2026-08-01',
            'endDate' => '2026-08-31',
        ])->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', AllocationStatus::Active->value)
            ->assertJsonPath('data.method', AllocationMethod::Manual->value)
            ->json('data');

        $this->getJson(allocationHttpUrl($created['allocationId']))
            ->assertOk()
            ->assertJsonPath('data.personId', $personId)
            ->assertJsonPath('data.bedId', $bedId);

        $this->getJson(allocationHttpUrl('active/'.$personId))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.allocationId', $created['allocationId']);

        $this->postJson(allocationHttpUrl($created['allocationId'].'/release'), [
            'reason' => 'Completed via HTTP',
        ])->assertOk()
            ->assertJsonPath('data.status', AllocationStatus::Released->value)
            ->assertJsonPath('data.releaseReason', 'Completed via HTTP');
    });

    it('returns not found for unknown allocation id', function (): void {
        $operator = createAllocationHttpOperator();
        authenticateAllocationHttpUser($operator['identity']);

        $this->getJson(allocationHttpUrl(UuidGenerator::uuid7()))
            ->assertNotFound()
            ->assertJsonPath('success', false);
    });
});

describe('http request-driven allocation flow', function (): void {
    it('creates an allocation from an approved request via http', function (): void {
        [$employee, $request, $bedId] = createApprovedPersonalRequestForAllocationTest();
        $operator = createAllocationHttpOperator();
        authenticateAllocationHttpUser($operator['identity']);

        $created = $this->postJson(allocationHttpUrl('from-request/'.$request->requireId()->value), [
            'bedId' => $bedId,
        ])->assertCreated()
            ->assertJsonPath('data.method', AllocationMethod::RequestSourced->value)
            ->assertJsonPath('data.sourceRequestId', $request->requireId()->value)
            ->assertJsonPath('data.personId', $employee->requireId()->value)
            ->json('data');

        expect(app(AllocationReadContract::class)->hasActiveAllocation($employee->requireId()->value))->toBeTrue();
        expect($created['bedId'])->toBe($bedId);
    });

    it('returns unprocessable when request is not approved', function (): void {
        $operator = createAllocationHttpOperator();
        authenticateAllocationHttpUser($operator['identity']);

        $this->postJson(allocationHttpUrl('from-request/'.UuidGenerator::uuid7()))
            ->assertUnprocessable()
            ->assertJsonPath('success', false);
    });
});

describe('http domain failures', function (): void {
    it('returns conflict when releasing an already released allocation', function (): void {
        $operator = createAllocationHttpOperator();
        authenticateAllocationHttpUser($operator['identity']);

        $personId = UuidGenerator::uuid7();
        $created = $this->postJson(allocationHttpUrl(), [
            'personId' => $personId,
            'bedId' => createAssignableBedForAllocationTests(),
            'startDate' => '2026-08-01',
            'endDate' => '2026-08-31',
        ])->assertCreated()
            ->json('data');

        $this->postJson(allocationHttpUrl($created['allocationId'].'/release'), [
            'reason' => 'First release',
        ])->assertOk();

        $this->postJson(allocationHttpUrl($created['allocationId'].'/release'), [
            'reason' => 'Duplicate release',
        ])->assertConflict()
            ->assertJsonPath('success', false);
    });

    it('returns conflict on overlapping active allocation for same person', function (): void {
        $operator = createAllocationHttpOperator();
        authenticateAllocationHttpUser($operator['identity']);

        $personId = UuidGenerator::uuid7();

        $this->postJson(allocationHttpUrl(), [
            'personId' => $personId,
            'bedId' => createAssignableBedForAllocationTests(),
            'startDate' => '2026-08-01',
            'endDate' => '2026-08-31',
        ])->assertCreated();

        $this->postJson(allocationHttpUrl(), [
            'personId' => $personId,
            'bedId' => createAssignableBedForAllocationTests('B2'),
            'startDate' => '2026-08-15',
            'endDate' => '2026-09-15',
        ])->assertConflict()
            ->assertJsonPath('success', false);
    });
});

describe('http authentication', function (): void {
    it('rejects unauthenticated allocation create', function (): void {
        $this->postJson(allocationHttpUrl(), [
            'personId' => UuidGenerator::uuid7(),
            'bedId' => UuidGenerator::uuid7(),
            'startDate' => '2026-08-01',
            'endDate' => '2026-08-31',
        ])->assertUnauthorized()
            ->assertJsonPath('success', false);
    });
});
