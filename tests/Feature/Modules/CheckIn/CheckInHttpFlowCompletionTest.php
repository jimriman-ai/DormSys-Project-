<?php

declare(strict_types=1);

use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\Allocation\Application\Services\CreateAllocationAction;
use App\Modules\CheckIn\Application\Contracts\CheckInRecordRepositoryContract;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

require_once __DIR__.'/support/http-mutation.php';
require_once __DIR__.'/../Allocation/support/http-mutation.php';

beforeEach(function (): void {
    Carbon::setTestNow('2026-07-01 12:00:00');
    app(MutationPrincipalContextHolder::class)->clear();
    request()->attributes->remove('audit_principal_user_id');
});

afterEach(function (): void {
    Carbon::setTestNow();
});

describe('http check-in flow', function (): void {
    it('checks in and out on an active allocation via http', function (): void {
        $checkInOperator = createCheckInHttpOperator();

        $allocationOperator = createAllocationHttpOperator();
        authenticateAllocationHttpUser($allocationOperator['identity']);

        $allocationId = $this->postJson(allocationHttpUrl(), [
            'personId' => UuidGenerator::uuid7(),
            'bedId' => UuidGenerator::uuid7(),
            'startDate' => '2026-08-01',
            'endDate' => '2026-08-31',
        ])->assertCreated()
            ->json('data.allocationId');

        authenticateCheckInHttpUser($checkInOperator['identity']);

        $checkedIn = $this->postJson(checkInHttpUrl($allocationId.'/check-in'))
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.allocationId', $allocationId)
            ->assertJsonPath('data.operatorId', $checkInOperator['principalId'])
            ->assertJsonPath('data.isCheckedOut', false)
            ->json('data');

        $this->getJson(checkInHttpUrl($allocationId))
            ->assertOk()
            ->assertJsonPath('data.checkInRecordId', $checkedIn['checkInRecordId'])
            ->assertJsonPath('data.isCheckedOut', false);

        $this->postJson(checkInHttpUrl($allocationId.'/check-out'))
            ->assertOk()
            ->assertJsonPath('data.isCheckedOut', true)
            ->assertJsonPath('data.checkedOutAt', fn ($value) => $value !== null);

        expect(app(CheckInRecordRepositoryContract::class)->findOpenByAllocationId($allocationId))->toBeNull();
    });
});

describe('http check-in domain failures', function (): void {
    it('returns not found when allocation is not active', function (): void {
        $operator = createCheckInHttpOperator();
        authenticateCheckInHttpUser($operator['identity']);

        $this->postJson(checkInHttpUrl(UuidGenerator::uuid7().'/check-in'))
            ->assertNotFound()
            ->assertJsonPath('success', false);
    });

    it('returns conflict on duplicate check-in', function (): void {
        $checkInOperator = createCheckInHttpOperator();

        $allocationOperator = createAllocationHttpOperator();
        authenticateAllocationHttpUser($allocationOperator['identity']);

        $allocationId = $this->postJson(allocationHttpUrl(), [
            'personId' => UuidGenerator::uuid7(),
            'bedId' => UuidGenerator::uuid7(),
            'startDate' => '2026-08-01',
            'endDate' => '2026-08-31',
        ])->assertCreated()
            ->json('data.allocationId');

        authenticateCheckInHttpUser($checkInOperator['identity']);

        $this->postJson(checkInHttpUrl($allocationId.'/check-in'))->assertCreated();

        $this->postJson(checkInHttpUrl($allocationId.'/check-in'))
            ->assertConflict()
            ->assertJsonPath('success', false);
    });

    it('returns not found when checking out without check-in', function (): void {
        $checkInOperator = createCheckInHttpOperator();

        $allocationOperator = createAllocationHttpOperator();
        authenticateAllocationHttpUser($allocationOperator['identity']);

        $allocationId = $this->postJson(allocationHttpUrl(), [
            'personId' => UuidGenerator::uuid7(),
            'bedId' => UuidGenerator::uuid7(),
            'startDate' => '2026-08-01',
            'endDate' => '2026-08-31',
        ])->assertCreated()
            ->json('data.allocationId');

        authenticateCheckInHttpUser($checkInOperator['identity']);

        $this->postJson(checkInHttpUrl($allocationId.'/check-out'))
            ->assertNotFound()
            ->assertJsonPath('success', false);
    });

    it('returns forbidden when user lacks operator role', function (): void {
        $allocationOperator = createAllocationHttpOperator();
        authenticateAllocationHttpUser($allocationOperator['identity']);

        $allocationId = $this->postJson(allocationHttpUrl(), [
            'personId' => UuidGenerator::uuid7(),
            'bedId' => UuidGenerator::uuid7(),
            'startDate' => '2026-08-01',
            'endDate' => '2026-08-31',
        ])->assertCreated()
            ->json('data.allocationId');

        $nonOperator = createAllocationHttpOperator();
        authenticateCheckInHttpUser($nonOperator['identity']);

        $this->postJson(checkInHttpUrl($allocationId.'/check-in'))
            ->assertForbidden()
            ->assertJsonPath('success', false);
    });
});

describe('http authentication', function (): void {
    it('rejects unauthenticated check-in', function (): void {
        $this->postJson(checkInHttpUrl(UuidGenerator::uuid7().'/check-in'))
            ->assertUnauthorized()
            ->assertJsonPath('success', false);
    });
});
