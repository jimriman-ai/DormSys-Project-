<?php

declare(strict_types=1);

use App\Modules\Dormitory\Domain\Enums\ResourceStatus;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\DormitoryAssignment;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\DormitoryModel;
use App\Modules\Identity\Domain\Enums\UserStatus;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Ramsey\Uuid\Uuid;

function createDormitoryPolicyIdentityUser(string $displayName = 'Dormitory Policy User'): UserModel
{
    $id = Uuid::uuid7()->toString();

    DB::table('identity_users')->insert([
        'id' => $id,
        'status' => UserStatus::Active->value,
        'display_name' => $displayName,
        'email' => 'dorm.policy.'.uniqid('', true).'@dormsys.local',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return UserModel::query()->findOrFail($id);
}

function createDormitoryPolicySite(string $code): DormitoryModel
{
    return DormitoryModel::query()->create([
        'code' => $code,
        'name' => 'Policy Site '.$code,
        'status' => ResourceStatus::Available,
    ]);
}

function createDormitoryPolicyAssignment(
    UserModel $user,
    DormitoryModel $dormitory,
    ?Carbon $revokedAt = null,
): DormitoryAssignment {
    return DormitoryAssignment::query()->create([
        'user_id' => $user->getId(),
        'dormitory_id' => $dormitory->getId(),
        'assigned_at' => now(),
        'revoked_at' => $revokedAt,
    ]);
}

it('allows viewAny and view when identity user has an active assignment', function (): void {
    $user = createDormitoryPolicyIdentityUser('Assigned Employee');
    $dormitory = createDormitoryPolicySite('POL-A');
    createDormitoryPolicyAssignment($user, $dormitory);

    $this->actingAs($user, 'identity');

    expect(Gate::forUser($user)->allows('viewAny', DormitoryModel::class))->toBeTrue()
        ->and(Gate::forUser($user)->allows('view', $dormitory))->toBeTrue();
});

it('denies viewAny and view when identity user has no assignment', function (): void {
    $user = createDormitoryPolicyIdentityUser('Unassigned Employee');
    $dormitory = createDormitoryPolicySite('POL-NONE');

    $this->actingAs($user, 'identity');

    expect(Gate::forUser($user)->denies('viewAny', DormitoryModel::class))->toBeTrue()
        ->and(Gate::forUser($user)->denies('view', $dormitory))->toBeTrue();
});

it('denies viewAny and view when assignment is revoked', function (): void {
    $user = createDormitoryPolicyIdentityUser('Revoked Employee');
    $dormitory = createDormitoryPolicySite('POL-REV');
    createDormitoryPolicyAssignment($user, $dormitory, now());

    $this->actingAs($user, 'identity');

    expect(Gate::forUser($user)->denies('viewAny', DormitoryModel::class))->toBeTrue()
        ->and(Gate::forUser($user)->denies('view', $dormitory))->toBeTrue();
});

it('denies view for a dormitory that is not the actively assigned one', function (): void {
    $user = createDormitoryPolicyIdentityUser('Cross-Site Employee');
    $dormA = createDormitoryPolicySite('POL-XA');
    $dormB = createDormitoryPolicySite('POL-XB');
    createDormitoryPolicyAssignment($user, $dormA);

    $this->actingAs($user, 'identity');

    expect(Gate::forUser($user)->allows('viewAny', DormitoryModel::class))->toBeTrue()
        ->and(Gate::forUser($user)->allows('view', $dormA))->toBeTrue()
        ->and(Gate::forUser($user)->denies('view', $dormB))->toBeTrue();
});
