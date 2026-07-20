<?php

declare(strict_types=1);

use App\Modules\Dormitory\Domain\Contracts\DormitoryAssignmentReader;
use App\Modules\Dormitory\Domain\Enums\ResourceStatus;
use App\Modules\Dormitory\Infrastructure\Persistence\EloquentDormitoryAssignmentReader;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\DormitoryAssignment;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\DormitoryModel;
use App\Modules\Identity\Domain\Enums\UserStatus;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

function createAssignmentReaderIdentityUser(): string
{
    $id = Uuid::uuid7()->toString();

    DB::table('identity_users')->insert([
        'id' => $id,
        'status' => UserStatus::Active->value,
        'display_name' => 'Assignment Reader User',
        'email' => 'assign.reader.'.uniqid('', true).'@dormsys.local',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return $id;
}

function createAssignmentReaderSite(string $code): DormitoryModel
{
    return DormitoryModel::query()->create([
        'code' => $code,
        'name' => 'Reader Site '.$code,
        'status' => ResourceStatus::Available,
    ]);
}

it('binds DormitoryAssignmentReader to the Eloquent adapter', function (): void {
    expect(app(DormitoryAssignmentReader::class))->toBeInstanceOf(EloquentDormitoryAssignmentReader::class);
});

it('reports active assignment presence matching policy semantics', function (): void {
    $userId = createAssignmentReaderIdentityUser();
    $dormA = createAssignmentReaderSite('RDR-A');
    $dormB = createAssignmentReaderSite('RDR-B');

    DormitoryAssignment::query()->create([
        'user_id' => $userId,
        'dormitory_id' => $dormA->getId(),
        'assigned_at' => now(),
        'revoked_at' => null,
    ]);

    /** @var DormitoryAssignmentReader $reader */
    $reader = app(DormitoryAssignmentReader::class);

    expect($reader->hasActiveAssignment($userId))->toBeTrue()
        ->and($reader->hasActiveAssignmentForDormitory($userId, $dormA->getId()))->toBeTrue()
        ->and($reader->hasActiveAssignmentForDormitory($userId, $dormB->getId()))->toBeFalse();
});

it('treats revoked assignments as inactive', function (): void {
    $userId = createAssignmentReaderIdentityUser();
    $dormitory = createAssignmentReaderSite('RDR-REV');

    DormitoryAssignment::query()->create([
        'user_id' => $userId,
        'dormitory_id' => $dormitory->getId(),
        'assigned_at' => now(),
        'revoked_at' => now(),
    ]);

    /** @var DormitoryAssignmentReader $reader */
    $reader = app(DormitoryAssignmentReader::class);

    expect($reader->hasActiveAssignment($userId))->toBeFalse()
        ->and($reader->hasActiveAssignmentForDormitory($userId, $dormitory->getId()))->toBeFalse();
});
