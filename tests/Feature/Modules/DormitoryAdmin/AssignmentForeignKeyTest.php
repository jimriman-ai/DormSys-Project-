<?php

declare(strict_types=1);

use App\Modules\Identity\Domain\Enums\UserStatus;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

/**
 * BL-B1-01 / RM-06: CONSTRAINED_IDENTITY FK enforcement on assignment tables.
 */
it('rejects manager assignment rows with invalid user_id FK', function (): void {
    expect(Schema::hasTable('dormitory_manager_assignments'))->toBeTrue();

    $dormitoryId = Uuid::uuid7()->toString();
    DB::table('dormitories')->insert([
        'id' => $dormitoryId,
        'code' => 'FK-MGR-'.substr($dormitoryId, 0, 8),
        'name' => 'FK Manager Dorm',
        'status' => 'available',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $invalidUserId = '019f0000-0000-7000-8000-000000000099';

    expect(fn () => DB::table('dormitory_manager_assignments')->insert([
        'id' => Uuid::uuid7()->toString(),
        'user_id' => $invalidUserId,
        'dormitory_id' => $dormitoryId,
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);
});

it('rejects unit-manager assignment rows with invalid user_id FK', function (): void {
    expect(Schema::hasTable('dormitory_unit_manager_assignments'))->toBeTrue();

    $now = now();
    $dormitoryId = Uuid::uuid7()->toString();
    $buildingId = Uuid::uuid7()->toString();
    $floorId = Uuid::uuid7()->toString();
    $roomId = Uuid::uuid7()->toString();

    DB::table('dormitories')->insert([
        'id' => $dormitoryId,
        'code' => 'FK-UNIT-'.substr($dormitoryId, 0, 8),
        'name' => 'FK Unit Dorm',
        'status' => 'available',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    DB::table('dormitory_buildings')->insert([
        'id' => $buildingId,
        'dormitory_id' => $dormitoryId,
        'code' => 'FKB',
        'name' => 'FK Building',
        'status' => 'available',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    DB::table('dormitory_floors')->insert([
        'id' => $floorId,
        'building_id' => $buildingId,
        'label' => '1',
        'status' => 'available',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    DB::table('dormitory_rooms')->insert([
        'id' => $roomId,
        'floor_id' => $floorId,
        'code' => 'FKR',
        'name' => 'FK Room',
        'capacity_total' => 1,
        'status' => 'available',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $invalidUserId = '019f0000-0000-7000-8000-000000000098';

    expect(fn () => DB::table('dormitory_unit_manager_assignments')->insert([
        'id' => Uuid::uuid7()->toString(),
        'user_id' => $invalidUserId,
        'room_id' => $roomId,
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);
});

it('accepts manager assignment when user_id references identity_users', function (): void {
    $userId = Uuid::uuid7()->toString();
    DB::table('identity_users')->insert([
        'id' => $userId,
        'status' => UserStatus::Active->value,
        'display_name' => 'FK Valid Manager',
        'email' => 'fk.valid.'.uniqid('', true).'@example.com',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $dormitoryId = Uuid::uuid7()->toString();
    DB::table('dormitories')->insert([
        'id' => $dormitoryId,
        'code' => 'FK-OK-'.substr($dormitoryId, 0, 8),
        'name' => 'FK Valid Dorm',
        'status' => 'available',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('dormitory_manager_assignments')->insert([
        'id' => Uuid::uuid7()->toString(),
        'user_id' => $userId,
        'dormitory_id' => $dormitoryId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(
        DB::table('dormitory_manager_assignments')->where('user_id', $userId)->exists()
    )->toBeTrue();
});
