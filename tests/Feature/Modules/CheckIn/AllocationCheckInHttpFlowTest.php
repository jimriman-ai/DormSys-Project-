<?php

declare(strict_types=1);

use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\Allocation\Domain\Enums\AllocationMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

require_once __DIR__.'/support/http-mutation.php';
require_once __DIR__.'/../Allocation/support/http-mutation.php';
require_once __DIR__.'/../Allocation/RequestDrivenAllocationTest.php';

beforeEach(function (): void {
    Carbon::setTestNow('2026-07-01 12:00:00');
    app(MutationPrincipalContextHolder::class)->clear();
    request()->attributes->remove('audit_principal_user_id');
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('checks in via http after request-driven allocation via http', function (): void {
    [$employee, $request, $bedId] = createApprovedPersonalRequestForAllocationTest();
    $checkInOperator = createCheckInHttpOperator();

    $allocationOperator = createAllocationHttpOperator();
    authenticateAllocationHttpUser($allocationOperator['identity']);

    $allocationId = $this->postJson(allocationHttpUrl('from-request/'.$request->requireId()->value), [
        'bedId' => $bedId,
    ])->assertCreated()
        ->assertJsonPath('data.method', AllocationMethod::RequestSourced->value)
        ->assertJsonPath('data.personId', $employee->requireId()->value)
        ->json('data.allocationId');

    authenticateCheckInHttpUser($checkInOperator['identity']);

    $this->postJson(checkInHttpUrl($allocationId.'/check-in'))
        ->assertCreated()
        ->assertJsonPath('data.allocationId', $allocationId)
        ->assertJsonPath('data.isCheckedOut', false);

    $this->getJson(checkInHttpUrl($allocationId))
        ->assertOk()
        ->assertJsonPath('data.allocationId', $allocationId);
});

it('does not modify allocation state when checking in via http', function (): void {
    [$employee, $request, $bedId] = createApprovedPersonalRequestForAllocationTest();
    $checkInOperator = createCheckInHttpOperator();

    $allocationOperator = createAllocationHttpOperator();
    authenticateAllocationHttpUser($allocationOperator['identity']);

    $allocationId = $this->postJson(allocationHttpUrl('from-request/'.$request->requireId()->value), [
        'bedId' => $bedId,
    ])->assertCreated()
        ->json('data');

    authenticateCheckInHttpUser($checkInOperator['identity']);

    $this->postJson(checkInHttpUrl($allocationId['allocationId'].'/check-in'))->assertCreated();

    authenticateAllocationHttpUser($allocationOperator['identity']);

    $this->getJson(allocationHttpUrl($allocationId['allocationId']))
        ->assertOk()
        ->assertJsonPath('data.status', $allocationId['status'])
        ->assertJsonPath('data.personId', $employee->requireId()->value)
        ->assertJsonPath('data.bedId', $bedId);
});
