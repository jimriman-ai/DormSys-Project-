<?php

declare(strict_types=1);

use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Modules\Lottery\Domain\States\LockedState;
use App\Modules\Lottery\Domain\States\RegistrationClosedState;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

require_once __DIR__.'/../Modules/Allocation/support/http-mutation.php';
require_once __DIR__.'/../Modules/CheckIn/support/http-mutation.php';
require_once __DIR__.'/../Modules/Lottery/LotteryFeatureSupport.php';
require_once __DIR__.'/../Modules/Lottery/support/enrollment.php';
require_once __DIR__.'/../Modules/Lottery/support/http-mutation.php';

beforeEach(function (): void {
    Carbon::setTestNow('2026-07-01 12:00:00');
    bootstrapLotteryFeatureTests();
    app(MutationPrincipalContextHolder::class)->clear();
    request()->attributes->remove('audit_principal_user_id');
});

afterEach(function (): void {
    Carbon::setTestNow();
    teardownLotteryFeatureTests();
});

describe('exception mapping stability', function (): void {
    it('maps allocation overlap to conflict on lottery draw without leaking internal errors', function (): void {
        $employee = createEmployeeForLotteryEnrollmentTest();
        $personId = $employee->requireId()->value;
        $dormitoryId = createDormitorySiteForRequestTests();
        createAssignableBedForAllocationTests(id: $dormitoryId);
        $requestId = createApprovedLotteryRegistrationRequest($employee, $dormitoryId);

        $allocationOperator = createAllocationHttpOperator();
        authenticateAllocationHttpUser($allocationOperator['identity']);

        $this->postJson(allocationHttpUrl(), [
            'personId' => $personId,
            'bedId' => createAssignableBedForAllocationTests('PRE'),
            'startDate' => '2026-08-01',
            'endDate' => '2026-08-31',
        ])->assertCreated();

        $lotteryOperator = createLotteryHttpOperator();
        authenticateLotteryHttpUser($lotteryOperator['identity']);

        $program = $this->postJson(lotteryHttpCreateProgramUrl(), [
            'title' => 'Overlap Draw Program',
            'dormitoryId' => $dormitoryId,
            'capacity' => 1,
            'registrationStartsAt' => '2026-07-01T00:00:00+00:00',
            'registrationEndsAt' => '2026-07-31T23:59:59+00:00',
        ])->assertCreated()
            ->json('data');

        $this->postJson(lotteryHttpProgramUrl($program['id'], 'open-registration'))->assertOk();

        authenticateLotteryHttpUser(
            UserModel::query()->findOrFail($employee->identityId->value),
        );

        $this->postJson(lotteryHttpProgramUrl($program['id'], 'enroll'), [
            'requestId' => $requestId,
        ])->assertCreated();

        authenticateLotteryHttpUser($lotteryOperator['identity']);

        $this->postJson(lotteryHttpProgramUrl($program['id'], 'close-registration'))
            ->assertOk()
            ->assertJsonPath('data.status', RegistrationClosedState::$name);

        $this->postJson(lotteryHttpProgramUrl($program['id'], 'lock'))
            ->assertOk()
            ->assertJsonPath('data.status', LockedState::$name);

        $this->postJson(lotteryHttpProgramUrl($program['id'], 'draw'))
            ->assertConflict()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'An overlapping allocation already exists for this person.');
    });

    it('maps duplicate lottery lock to conflict without internal exception leakage', function (): void {
        $operator = createLotteryHttpOperator();
        authenticateLotteryHttpUser($operator['identity']);

        $program = $this->postJson(lotteryHttpCreateProgramUrl(), [
            'title' => 'Duplicate Lock Program',
            'dormitoryId' => createDormitorySiteForRequestTests(),
            'capacity' => 1,
            'registrationStartsAt' => '2026-07-01T00:00:00+00:00',
            'registrationEndsAt' => '2026-07-31T23:59:59+00:00',
        ])->assertCreated()
            ->json('data');

        $this->postJson(lotteryHttpProgramUrl($program['id'], 'open-registration'))->assertOk();
        $this->postJson(lotteryHttpProgramUrl($program['id'], 'close-registration'))->assertOk();
        $this->postJson(lotteryHttpProgramUrl($program['id'], 'lock'))->assertOk();

        $this->postJson(lotteryHttpProgramUrl($program['id'], 'lock'))
            ->assertConflict()
            ->assertJsonPath('success', false);
    });

    it('maps allocation overlap to conflict on allocation root create path', function (): void {
        $operator = createAllocationHttpOperator();
        authenticateAllocationHttpUser($operator['identity']);

        $personId = UuidGenerator::uuid7();

        $this->postJson(allocationHttpUrl(), [
            'personId' => $personId,
            'bedId' => createAssignableBedForAllocationTests('B1'),
            'startDate' => '2026-08-01',
            'endDate' => '2026-08-31',
        ])->assertCreated();

        $this->postJson(allocationHttpUrl(), [
            'personId' => $personId,
            'bedId' => createAssignableBedForAllocationTests('B2'),
            'startDate' => '2026-08-15',
            'endDate' => '2026-09-15',
        ])->assertConflict()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'An overlapping allocation already exists for this person.');
    });
});

describe('response consistency', function (): void {
    it('always returns persisted check-in record id after successful check-in', function (): void {
        $checkInOperator = createCheckInHttpOperator();

        $allocationOperator = createAllocationHttpOperator();
        authenticateAllocationHttpUser($allocationOperator['identity']);

        $allocationId = $this->postJson(allocationHttpUrl(), [
            'personId' => UuidGenerator::uuid7(),
            'bedId' => createAssignableBedForAllocationTests(),
            'startDate' => '2026-08-01',
            'endDate' => '2026-08-31',
        ])->assertCreated()
            ->json('data.allocationId');

        authenticateCheckInHttpUser($checkInOperator['identity']);

        $this->postJson(checkInHttpUrl($allocationId.'/check-in'))
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.checkInRecordId', fn ($value) => is_string($value) && $value !== '');
    });
});

describe('lottery snapshot boundary runtime guard', function (): void {
    it('rejects lock when an eligible snapshot already exists even if program status was rewound', function (): void {
        $operator = createLotteryHttpOperator();
        authenticateLotteryHttpUser($operator['identity']);

        $program = $this->postJson(lotteryHttpCreateProgramUrl(), [
            'title' => 'Snapshot Guard Program',
            'dormitoryId' => createDormitorySiteForRequestTests(),
            'capacity' => 1,
            'registrationStartsAt' => '2026-07-01T00:00:00+00:00',
            'registrationEndsAt' => '2026-07-31T23:59:59+00:00',
        ])->assertCreated()
            ->json('data');

        $this->postJson(lotteryHttpProgramUrl($program['id'], 'open-registration'))->assertOk();
        $this->postJson(lotteryHttpProgramUrl($program['id'], 'close-registration'))->assertOk();
        $this->postJson(lotteryHttpProgramUrl($program['id'], 'lock'))->assertOk();

        App\Modules\Lottery\Infrastructure\Persistence\Models\LotteryProgramModel::query()
            ->whereKey($program['id'])
            ->update(['status' => RegistrationClosedState::$name]);

        expect(fn () => runLotteryMutation(fn () => app(App\Modules\Lottery\Application\Services\LockLotteryProgramAction::class)->execute(
            App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId::fromString($program['id']),
        )))->toThrow(
            App\Modules\Lottery\Domain\Exceptions\InvalidLotteryTransitionException::class,
            'Eligible snapshot already captured for this program.',
        );
    });
});
