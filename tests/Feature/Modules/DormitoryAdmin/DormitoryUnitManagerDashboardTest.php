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

function createUnitManagerIdentityUser(string $displayName = 'Unit Manager'): UserModel
{
    $id = Uuid::uuid7()->toString();

    DB::table('identity_users')->insert([
        'id' => $id,
        'status' => UserStatus::Active->value,
        'display_name' => $displayName,
        'email' => 'unit.mgr.'.uniqid('', true).'@example.com',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return UserModel::query()->findOrFail($id);
}

function assignUnitManagerRole(UserModel $user, string $roleName): void
{
    $role = Role::findOrCreate($roleName, 'identity');
    $user->assignRole($role);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
}

/**
 * @return array{
 *     dormitory_id: string,
 *     building_id: string,
 *     floor_id: string,
 *     room_a_id: string,
 *     room_b_id: string,
 *     room_a_name: string,
 *     room_b_name: string
 * }
 */
function seedUnitManagerHierarchy(string $dormName = 'Unit Dorm'): array
{
    $now = now();
    $dormitoryId = Uuid::uuid7()->toString();
    $buildingId = Uuid::uuid7()->toString();
    $floorId = Uuid::uuid7()->toString();
    $roomAId = Uuid::uuid7()->toString();
    $roomBId = Uuid::uuid7()->toString();
    $roomAName = 'Unit Room Alpha';
    $roomBName = 'Unit Room Beta';

    DB::table('dormitories')->insert([
        'id' => $dormitoryId,
        'code' => 'UD-'.substr($dormitoryId, 0, 8),
        'name' => $dormName,
        'status' => 'available',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('dormitory_buildings')->insert([
        'id' => $buildingId,
        'dormitory_id' => $dormitoryId,
        'code' => 'UB1',
        'name' => 'Unit Building 1',
        'status' => 'available',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('dormitory_floors')->insert([
        'id' => $floorId,
        'building_id' => $buildingId,
        'label' => '2',
        'status' => 'available',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('dormitory_rooms')->insert([
        [
            'id' => $roomAId,
            'floor_id' => $floorId,
            'code' => 'URA',
            'name' => $roomAName,
            'capacity_total' => 4,
            'status' => 'available',
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'id' => $roomBId,
            'floor_id' => $floorId,
            'code' => 'URB',
            'name' => $roomBName,
            'capacity_total' => 2,
            'status' => 'available',
            'created_at' => $now,
            'updated_at' => $now,
        ],
    ]);

    return [
        'dormitory_id' => $dormitoryId,
        'building_id' => $buildingId,
        'floor_id' => $floorId,
        'room_a_id' => $roomAId,
        'room_b_id' => $roomBId,
        'room_a_name' => $roomAName,
        'room_b_name' => $roomBName,
    ];
}

function assignUnitManagerToRoom(string $userId, string $roomId): void
{
    DB::table('dormitory_unit_manager_assignments')->insert([
        'id' => Uuid::uuid7()->toString(),
        'user_id' => $userId,
        'room_id' => $roomId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function seedBedsForRoom(string $roomId, int $occupied, int $reserved, int $vacant): void
{
    $now = now();
    $beds = [];

    for ($i = 0; $i < $occupied; $i++) {
        $beds[] = [
            'id' => Uuid::uuid7()->toString(),
            'room_id' => $roomId,
            'label' => 'OCC-'.$i,
            'status' => 'available',
            'physical_occupancy_state' => 'occupied',
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
    for ($i = 0; $i < $reserved; $i++) {
        $beds[] = [
            'id' => Uuid::uuid7()->toString(),
            'room_id' => $roomId,
            'label' => 'RES-'.$i,
            'status' => 'available',
            'physical_occupancy_state' => 'reserved',
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
    for ($i = 0; $i < $vacant; $i++) {
        $beds[] = [
            'id' => Uuid::uuid7()->toString(),
            'room_id' => $roomId,
            'label' => 'VAC-'.$i,
            'status' => 'available',
            'physical_occupancy_state' => 'vacant',
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    if ($beds !== []) {
        DB::table('dormitory_beds')->insert($beds);
    }
}

it('redirects guests from the unit manager dashboard', function (): void {
    $this->get('/dormitory-admin/unit')->assertRedirect('/login');
});

it('forbids authenticated identity users without dormitory-unit-manager role', function (): void {
    $user = createUnitManagerIdentityUser('No Unit Role');

    $this->actingAs($user, 'identity')
        ->get('/dormitory-admin/unit')
        ->assertForbidden();
});

it('forbids dormitory-manager-only users from the unit dashboard', function (): void {
    $user = createUnitManagerIdentityUser('Manager Only');
    assignUnitManagerRole($user, IdentityRoleSeeder::ROLE_DORMITORY_MANAGER);

    $this->actingAs($user, 'identity')
        ->get('/dormitory-admin/unit')
        ->assertForbidden();
});

it('allows dormitory-unit-manager with empty assignments', function (): void {
    $user = createUnitManagerIdentityUser('Empty Unit Manager');
    assignUnitManagerRole($user, IdentityRoleSeeder::ROLE_DORMITORY_UNIT_MANAGER);

    $this->actingAs($user, 'identity')
        ->get('/dormitory-admin/unit')
        ->assertOk()
        ->assertSee('اتاقی به شما اختصاص داده نشده است.', false)
        ->assertSee('خارج از محدوده — Stage 3', false);
});

it('scopes unit dashboard to assigned rooms only', function (): void {
    $user = createUnitManagerIdentityUser('Scoped Unit Manager');
    assignUnitManagerRole($user, IdentityRoleSeeder::ROLE_DORMITORY_UNIT_MANAGER);

    $hierarchy = seedUnitManagerHierarchy('Scope Dorm');
    seedBedsForRoom($hierarchy['room_a_id'], occupied: 1, reserved: 0, vacant: 1);
    seedBedsForRoom($hierarchy['room_b_id'], occupied: 2, reserved: 1, vacant: 0);

    assignUnitManagerToRoom($user->id, $hierarchy['room_a_id']);

    $this->actingAs($user, 'identity')
        ->get('/dormitory-admin/unit')
        ->assertOk()
        ->assertSee($hierarchy['room_a_name'], false)
        ->assertDontSee($hierarchy['room_b_name'], false);
});

it('counts occupied reserved and vacant independently', function (): void {
    $user = createUnitManagerIdentityUser('Counts Unit Manager');
    assignUnitManagerRole($user, IdentityRoleSeeder::ROLE_DORMITORY_UNIT_MANAGER);

    $hierarchy = seedUnitManagerHierarchy('Counts Dorm');
    seedBedsForRoom($hierarchy['room_a_id'], occupied: 2, reserved: 1, vacant: 3);
    assignUnitManagerToRoom($user->id, $hierarchy['room_a_id']);

    $html = $this->actingAs($user, 'identity')
        ->get('/dormitory-admin/unit')
        ->assertOk()
        ->assertSee($hierarchy['room_a_name'], false)
        ->assertSee('خارج از محدوده — Stage 3', false)
        ->getContent();

    expect($html)
        ->toContain('data-testid="bed-total">6</dd>')
        ->toContain('data-testid="bed-occupied">2</dd>')
        ->toContain('data-testid="bed-reserved">1</dd>')
        ->toContain('data-testid="bed-vacant">3</dd>');
});

it('renders zero-bed assigned rooms with zero counts', function (): void {
    $user = createUnitManagerIdentityUser('Zero Bed Unit Manager');
    assignUnitManagerRole($user, IdentityRoleSeeder::ROLE_DORMITORY_UNIT_MANAGER);

    $hierarchy = seedUnitManagerHierarchy('Zero Bed Dorm');
    assignUnitManagerToRoom($user->id, $hierarchy['room_a_id']);

    $html = $this->actingAs($user, 'identity')
        ->get('/dormitory-admin/unit')
        ->assertOk()
        ->assertSee($hierarchy['room_a_name'], false)
        ->getContent();

    expect($html)
        ->toContain('data-testid="bed-total">0</dd>')
        ->toContain('data-testid="bed-occupied">0</dd>')
        ->toContain('data-testid="bed-reserved">0</dd>')
        ->toContain('data-testid="bed-vacant">0</dd>');
});

it('leaves dormitory manager route accessible only to dormitory-manager', function (): void {
    $manager = createUnitManagerIdentityUser('Still Manager');
    assignUnitManagerRole($manager, IdentityRoleSeeder::ROLE_DORMITORY_MANAGER);

    $unitOnly = createUnitManagerIdentityUser('Unit Only On Manager Route');
    assignUnitManagerRole($unitOnly, IdentityRoleSeeder::ROLE_DORMITORY_UNIT_MANAGER);

    $this->actingAs($manager, 'identity')
        ->get('/dormitory-admin')
        ->assertOk();

    $this->actingAs($unitOnly, 'identity')
        ->get('/dormitory-admin')
        ->assertForbidden();
});
