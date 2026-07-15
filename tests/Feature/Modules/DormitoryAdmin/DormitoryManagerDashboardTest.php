<?php

declare(strict_types=1);

use App\Modules\Identity\Domain\Enums\UserStatus;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

function createDormitoryAdminIdentityUser(string $displayName = 'Dorm Manager'): UserModel
{
    $id = Uuid::uuid7()->toString();

    DB::table('identity_users')->insert([
        'id' => $id,
        'status' => UserStatus::Active->value,
        'display_name' => $displayName,
        'email' => 'dorm.mgr.'.uniqid('', true).'@example.com',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return UserModel::query()->findOrFail($id);
}

/**
 * Prefer Spatie assignRole (dual-guard). Falls back is not used — reports via test outcome.
 */
function assignIdentityGuardRole(UserModel $user, string $roleName): void
{
    $role = Role::findOrCreate($roleName, 'identity');
    $user->assignRole($role);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
}

/**
 * @return array{dormitory_id: string, name: string}
 */
function seedDormitoryHierarchyForDashboard(
    string $name,
    string $code,
    int $capacityA,
    int $capacityB,
    int $occupiedBeds,
    int $nonOccupiedBeds,
): array {
    $now = now();
    $dormitoryId = Uuid::uuid7()->toString();
    $buildingId = Uuid::uuid7()->toString();
    $floorId = Uuid::uuid7()->toString();
    $roomAId = Uuid::uuid7()->toString();
    $roomBId = Uuid::uuid7()->toString();

    DB::table('dormitories')->insert([
        'id' => $dormitoryId,
        'code' => $code,
        'name' => $name,
        'status' => 'available',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('dormitory_buildings')->insert([
        'id' => $buildingId,
        'dormitory_id' => $dormitoryId,
        'code' => 'B1',
        'name' => 'Building 1',
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
        [
            'id' => $roomAId,
            'floor_id' => $floorId,
            'code' => 'R-A',
            'name' => 'Room A',
            'capacity_total' => $capacityA,
            'status' => 'available',
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'id' => $roomBId,
            'floor_id' => $floorId,
            'code' => 'R-B',
            'name' => 'Room B',
            'capacity_total' => $capacityB,
            'status' => 'available',
            'created_at' => $now,
            'updated_at' => $now,
        ],
    ]);

    $beds = [];
    for ($i = 0; $i < $occupiedBeds; $i++) {
        $beds[] = [
            'id' => Uuid::uuid7()->toString(),
            'room_id' => $roomAId,
            'label' => 'OCC-'.$i,
            'status' => 'available',
            'physical_occupancy_state' => 'occupied',
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
    for ($i = 0; $i < $nonOccupiedBeds; $i++) {
        $beds[] = [
            'id' => Uuid::uuid7()->toString(),
            'room_id' => $roomBId,
            'label' => 'VAC-'.$i,
            'status' => 'available',
            'physical_occupancy_state' => $i % 2 === 0 ? 'vacant' : 'reserved',
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    DB::table('dormitory_beds')->insert($beds);

    return ['dormitory_id' => $dormitoryId, 'name' => $name];
}

function assignManagerToDormitory(string $userId, string $dormitoryId): void
{
    DB::table('dormitory_manager_assignments')->insert([
        'id' => Uuid::uuid7()->toString(),
        'dormitory_id' => $dormitoryId,
        'user_id' => $userId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

it('redirects guests from the dormitory manager dashboard', function (): void {
    $this->get('/dormitory-admin')->assertRedirect('/login');
});

it('forbids authenticated identity users without dormitory-manager role', function (): void {
    $user = createDormitoryAdminIdentityUser('No Role User');

    $this->actingAs($user, 'identity')
        ->get('/dormitory-admin')
        ->assertForbidden();
});

it('shows empty state for dormitory-manager with no assignments', function (): void {
    $user = createDormitoryAdminIdentityUser('Empty Manager');
    assignIdentityGuardRole($user, IdentityRoleSeeder::ROLE_DORMITORY_MANAGER);

    $this->actingAs($user, 'identity')
        ->get('/dormitory-admin')
        ->assertOk()
        ->assertSee('خوابگاهی به شما اختصاص داده نشده است.', false)
        ->assertSee('خارج از محدوده — Stage 3', false);
});

it('scopes dashboard to assigned dormitories only', function (): void {
    $user = createDormitoryAdminIdentityUser('Scoped Manager');
    assignIdentityGuardRole($user, IdentityRoleSeeder::ROLE_DORMITORY_MANAGER);

    $dormA = seedDormitoryHierarchyForDashboard(
        name: 'Dormitory Alpha Scoped',
        code: 'DORM-A-SCOPE',
        capacityA: 4,
        capacityB: 2,
        occupiedBeds: 1,
        nonOccupiedBeds: 1,
    );
    seedDormitoryHierarchyForDashboard(
        name: 'Dormitory Beta Scoped',
        code: 'DORM-B-SCOPE',
        capacityA: 3,
        capacityB: 3,
        occupiedBeds: 2,
        nonOccupiedBeds: 2,
    );

    assignManagerToDormitory($user->id, $dormA['dormitory_id']);

    $this->actingAs($user, 'identity')
        ->get('/dormitory-admin')
        ->assertOk()
        ->assertSee('Dormitory Alpha Scoped', false)
        ->assertDontSee('Dormitory Beta Scoped', false);
});

it('reports correct unit and occupancy counts for assigned dormitory', function (): void {
    $user = createDormitoryAdminIdentityUser('Counts Manager');
    assignIdentityGuardRole($user, IdentityRoleSeeder::ROLE_DORMITORY_MANAGER);

    $dorm = seedDormitoryHierarchyForDashboard(
        name: 'Dormitory Counts Fixture',
        code: 'DORM-COUNTS',
        capacityA: 4,
        capacityB: 2,
        occupiedBeds: 3,
        nonOccupiedBeds: 2,
    );

    assignManagerToDormitory($user->id, $dorm['dormitory_id']);

    $html = $this->actingAs($user, 'identity')
        ->get('/dormitory-admin')
        ->assertOk()
        ->assertSee('Dormitory Counts Fixture', false)
        ->assertSee('خارج از محدوده — Stage 3', false)
        ->getContent();

    expect($html)
        ->toContain('data-testid="unit-count">2</dd>')
        ->toContain('data-testid="bed-total">5</dd>')
        ->toContain('data-testid="bed-occupied">3</dd>')
        ->toContain('data-testid="bed-available">2</dd>');
});
