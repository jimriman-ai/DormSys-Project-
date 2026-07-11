<?php

declare(strict_types=1);

use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Modules\Lottery\Domain\States\CompletedState;
use App\Modules\Lottery\Domain\States\DraftState;
use App\Modules\Lottery\Domain\States\LockedState;
use App\Modules\Lottery\Domain\States\RegistrationClosedState;
use App\Modules\Lottery\Domain\States\RegistrationOpenState;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

require_once __DIR__.'/LotteryFeatureSupport.php';
require_once __DIR__.'/LotteryRegistrationEnrollmentTest.php';

beforeEach(function (): void {
    bootstrapLotteryFeatureTests();
    app(MutationPrincipalContextHolder::class)->clear();
    request()->attributes->remove('audit_principal_user_id');
});

afterEach(function (): void {
    teardownLotteryFeatureTests();
});

describe('http program setup', function (): void {
    it('creates a draft lottery program via http', function (): void {
        $operator = createLotteryHttpOperator();
        authenticateLotteryHttpUser($operator['identity']);

        $dormitoryId = createDormitorySiteForRequestTests();

        $this->postJson(lotteryHttpCreateProgramUrl(), [
            'title' => 'HTTP Lottery Program',
            'dormitoryId' => $dormitoryId,
            'capacity' => 2,
            'registrationStartsAt' => '2026-07-01T00:00:00+00:00',
            'registrationEndsAt' => '2026-07-31T23:59:59+00:00',
        ])->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', DraftState::$name)
            ->assertJsonPath('data.dormitoryId', $dormitoryId)
            ->assertJsonPath('data.capacity', 2);
    });

    it('shows an existing lottery program via http', function (): void {
        $operator = createLotteryHttpOperator();
        authenticateLotteryHttpUser($operator['identity']);

        $created = $this->postJson(lotteryHttpCreateProgramUrl(), [
            'title' => 'Show Test Program',
            'dormitoryId' => createDormitorySiteForRequestTests(),
            'capacity' => 1,
            'registrationStartsAt' => '2026-07-01T00:00:00+00:00',
            'registrationEndsAt' => '2026-07-31T23:59:59+00:00',
        ])->assertCreated()
            ->json('data');

        $this->getJson(lotteryHttpProgramUrl($created['id']))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $created['id'])
            ->assertJsonPath('data.status', DraftState::$name);
    });

    it('returns not found for unknown program id on show', function (): void {
        $operator = createLotteryHttpOperator();
        authenticateLotteryHttpUser($operator['identity']);

        $this->getJson(lotteryHttpProgramUrl(UuidGenerator::uuid7()))
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Lottery program not found.');
    });
});

describe('http end-to-end lottery flow', function (): void {
    it('runs create open enroll close lock draw and reads results via http', function (): void {
        $operator = createLotteryHttpOperator();
        authenticateLotteryHttpUser($operator['identity']);

        $dormitoryId = createDormitorySiteForRequestTests();

        $program = $this->postJson(lotteryHttpCreateProgramUrl(), [
            'title' => 'HTTP E2E Program',
            'dormitoryId' => $dormitoryId,
            'capacity' => 1,
            'registrationStartsAt' => '2026-07-01T00:00:00+00:00',
            'registrationEndsAt' => '2026-07-31T23:59:59+00:00',
        ])->assertCreated()
            ->json('data');

        $opened = $this->postJson(lotteryHttpProgramUrl($program['id'], 'open-registration'))
            ->assertOk()
            ->assertJsonPath('data.status', RegistrationOpenState::$name)
            ->json('data');

        $employeeOne = createEmployeeForLotteryEnrollmentTest();
        $employeeTwo = Tests\Feature\Modules\Lottery\LotteryTestFactory::createSecondEmployee();
        $requestOne = createApprovedLotteryRegistrationRequest($employeeOne, $dormitoryId);
        $requestTwo = createApprovedLotteryRegistrationRequest($employeeTwo, $dormitoryId);

        authenticateLotteryHttpUser(
            UserModel::query()->findOrFail($employeeOne->identityId->value),
        );

        $this->postJson(lotteryHttpProgramUrl($opened['id'], 'enroll'), [
            'requestId' => $requestOne,
        ])->assertCreated()
            ->assertJsonPath('data.requestId', $requestOne);

        authenticateLotteryHttpUser(
            UserModel::query()->findOrFail($employeeTwo->identityId->value),
        );

        $this->postJson(lotteryHttpProgramUrl($opened['id'], 'enroll'), [
            'requestId' => $requestTwo,
        ])->assertCreated();

        authenticateLotteryHttpUser($operator['identity']);

        $closed = $this->postJson(lotteryHttpProgramUrl($opened['id'], 'close-registration'))
            ->assertOk()
            ->assertJsonPath('data.status', RegistrationClosedState::$name)
            ->json('data');

        $locked = $this->postJson(lotteryHttpProgramUrl($closed['id'], 'lock'))
            ->assertOk()
            ->assertJsonPath('data.status', LockedState::$name)
            ->json('data');

        expect($locked['randomSeed'])->not->toBeNull();

        $completed = $this->postJson(lotteryHttpProgramUrl($locked['id'], 'draw'))
            ->assertOk()
            ->assertJsonPath('data.status', CompletedState::$name)
            ->json('data');

        $results = $this->getJson(lotteryHttpProgramUrl($completed['id'], 'results'))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->json('data');

        assertLotteryResultReadContractShape($results);
        expect($results['program_id'])->toBe($completed['id']);
        expect($results['winners'])->toHaveCount(1);
        expect($results['reserves'])->toHaveCount(1);

        $this->postJson(lotteryHttpProgramUrl($completed['id'], 'draw'))
            ->assertOk()
            ->assertJsonPath('data.status', CompletedState::$name);
    });
});

describe('http domain failures', function (): void {
    it('returns conflict when opening registration twice', function (): void {
        $operator = createLotteryHttpOperator();
        authenticateLotteryHttpUser($operator['identity']);

        $program = $this->postJson(lotteryHttpCreateProgramUrl(), [
            'title' => 'Conflict Program',
            'dormitoryId' => createDormitorySiteForRequestTests(),
            'capacity' => 1,
            'registrationStartsAt' => '2026-07-01T00:00:00+00:00',
            'registrationEndsAt' => '2026-07-31T23:59:59+00:00',
        ])->assertCreated()
            ->json('data');

        $this->postJson(lotteryHttpProgramUrl($program['id'], 'open-registration'))
            ->assertOk();

        $this->postJson(lotteryHttpProgramUrl($program['id'], 'open-registration'))
            ->assertConflict()
            ->assertJsonPath('success', false);
    });

    it('returns unprocessable when enrolling without approved request', function (): void {
        $operator = createLotteryHttpOperator();
        authenticateLotteryHttpUser($operator['identity']);

        $program = $this->postJson(lotteryHttpCreateProgramUrl(), [
            'title' => 'Enroll Failure Program',
            'dormitoryId' => createDormitorySiteForRequestTests(),
            'capacity' => 1,
            'registrationStartsAt' => '2026-07-01T00:00:00+00:00',
            'registrationEndsAt' => '2026-07-31T23:59:59+00:00',
        ])->assertCreated()
            ->json('data');

        $this->postJson(lotteryHttpProgramUrl($program['id'], 'open-registration'))
            ->assertOk();

        $employee = createEmployeeForLotteryEnrollmentTest();
        authenticateLotteryHttpUser(
            UserModel::query()->findOrFail($employee->identityId->value),
        );

        $this->postJson(lotteryHttpProgramUrl($program['id'], 'enroll'), [
            'requestId' => UuidGenerator::uuid7(),
        ])->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Approved lottery registration request not found.');
    });

    it('denies enrollment when principal does not own the request', function (): void {
        $operator = createLotteryHttpOperator();
        authenticateLotteryHttpUser($operator['identity']);

        $dormitoryId = createDormitorySiteForRequestTests();
        $program = $this->postJson(lotteryHttpCreateProgramUrl(), [
            'title' => 'Ownership Program',
            'dormitoryId' => $dormitoryId,
            'capacity' => 1,
            'registrationStartsAt' => '2026-07-01T00:00:00+00:00',
            'registrationEndsAt' => '2026-07-31T23:59:59+00:00',
        ])->assertCreated()
            ->json('data');

        $this->postJson(lotteryHttpProgramUrl($program['id'], 'open-registration'))
            ->assertOk();

        $owner = createEmployeeForLotteryEnrollmentTest();
        $other = Tests\Feature\Modules\Lottery\LotteryTestFactory::createSecondEmployee();
        $requestId = createApprovedLotteryRegistrationRequest($owner, $dormitoryId);

        authenticateLotteryHttpUser(
            UserModel::query()->findOrFail($other->identityId->value),
        );

        $this->postJson(lotteryHttpProgramUrl($program['id'], 'enroll'), [
            'requestId' => $requestId,
        ])->assertForbidden()
            ->assertJsonPath('message', 'Mutation actor must own the enrollment request.');
    });
});

describe('http authentication', function (): void {
    it('rejects unauthenticated program create', function (): void {
        $this->postJson(lotteryHttpCreateProgramUrl(), [
            'title' => 'Unauthenticated',
            'dormitoryId' => createDormitorySiteForRequestTests(),
            'capacity' => 1,
            'registrationStartsAt' => '2026-07-01T00:00:00+00:00',
            'registrationEndsAt' => '2026-07-31T23:59:59+00:00',
        ])->assertUnauthorized()
            ->assertJsonPath('success', false);
    });
});
