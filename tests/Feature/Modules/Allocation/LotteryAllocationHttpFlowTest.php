<?php

declare(strict_types=1);

use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\Allocation\Application\Contracts\AllocationRepositoryContract;
use App\Modules\Allocation\Domain\Enums\AllocationMethod;
use App\Modules\Allocation\Domain\Enums\AllocationStatus;
use App\Modules\Allocation\Domain\ValueObjects\AllocationId;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Modules\Lottery\Domain\States\CompletedState;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Modules\Lottery\LotteryTestFactory;

uses(RefreshDatabase::class);

require_once __DIR__.'/../Lottery/LotteryFeatureSupport.php';
require_once __DIR__.'/../Lottery/LotteryRegistrationEnrollmentTest.php';

beforeEach(function (): void {
    bootstrapLotteryFeatureTests();
    app(MutationPrincipalContextHolder::class)->clear();
    request()->attributes->remove('audit_principal_user_id');
});

afterEach(function (): void {
    teardownLotteryFeatureTests();
});

it('persists lottery-sourced allocations from http draw using frozen snapshot winners only', function (): void {
    $operator = createLotteryHttpOperator();
    authenticateLotteryHttpUser($operator['identity']);

    $dormitoryId = UuidGenerator::uuid7();

    $program = $this->postJson(lotteryHttpCreateProgramUrl(), [
        'title' => 'Allocation Intake Program',
        'dormitoryId' => $dormitoryId,
        'capacity' => 1,
        'registrationStartsAt' => '2026-07-01T00:00:00+00:00',
        'registrationEndsAt' => '2026-07-31T23:59:59+00:00',
    ])->assertCreated()
        ->json('data');

    $this->postJson(lotteryHttpProgramUrl($program['id'], 'open-registration'))->assertOk();

    $employeeOne = createEmployeeForLotteryEnrollmentTest();
    $employeeTwo = LotteryTestFactory::createSecondEmployee();
    $requestOne = createApprovedLotteryRegistrationRequest($employeeOne, $dormitoryId);
    $requestTwo = createApprovedLotteryRegistrationRequest($employeeTwo, $dormitoryId);

    authenticateLotteryHttpUser(UserModel::query()->findOrFail($employeeOne->identityId->value));
    $registrationOne = $this->postJson(lotteryHttpProgramUrl($program['id'], 'enroll'), [
        'requestId' => $requestOne,
    ])->assertCreated()->json('data');

    authenticateLotteryHttpUser(UserModel::query()->findOrFail($employeeTwo->identityId->value));
    $this->postJson(lotteryHttpProgramUrl($program['id'], 'enroll'), [
        'requestId' => $requestTwo,
    ])->assertCreated();

    authenticateLotteryHttpUser($operator['identity']);
    $this->postJson(lotteryHttpProgramUrl($program['id'], 'close-registration'))->assertOk();
    $this->postJson(lotteryHttpProgramUrl($program['id'], 'lock'))->assertOk();

    $completed = $this->postJson(lotteryHttpProgramUrl($program['id'], 'draw'))
        ->assertOk()
        ->assertJsonPath('data.status', CompletedState::$name)
        ->json('data');

    $results = $this->getJson(lotteryHttpProgramUrl($completed['id'], 'results'))
        ->assertOk()
        ->json('data');

    $winnerRegistrationId = $results['winners'][0]['registration_id'];
    $winnerEmployeeId = $winnerRegistrationId === $registrationOne['id']
        ? $employeeOne->requireId()->value
        : $employeeTwo->requireId()->value;

    $allocationId = DB::table('allocations')
        ->where('person_id', $winnerEmployeeId)
        ->value('id');

    expect($allocationId)->not->toBeNull();

    $allocation = app(AllocationRepositoryContract::class)->findById(
        AllocationId::fromString((string) $allocationId),
    );

    expect($allocation)->not->toBeNull();
    $allocation = $allocation ?? throw new RuntimeException('Allocation not found');
    expect($allocation->status)->toBe(AllocationStatus::Active);
    expect($allocation->method)->toBe(AllocationMethod::LotterySourced);
    expect($allocation->sourceLotteryResultId)->toBe($winnerRegistrationId);
    expect($allocation->bedId)->toBe($dormitoryId);

    authenticateAllocationHttpUser($operator['identity']);

    $this->getJson(allocationHttpUrl((string) $allocationId))
        ->assertOk()
        ->assertJsonPath('data.method', AllocationMethod::LotterySourced->value)
        ->assertJsonPath('data.sourceLotteryResultId', $winnerRegistrationId);
});
