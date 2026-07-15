<?php

declare(strict_types=1);

use App\Modules\Dormitory\Application\Contracts\DormitoryStructureMutationContract;
use App\Modules\Dormitory\Application\Contracts\DormitoryStructureReadContract;
use App\Modules\Dormitory\Application\DTOs\CreateDormitoryData;
use App\Modules\Dormitory\Application\Exceptions\UnauthorizedDormitoryStructureAccessException;
use App\Modules\Identity\Application\Authorization\DormitoryStructurePermissionCatalog;
use Database\Seeders\IdentityRoleSeeder;
use Spatie\Permission\Models\Permission;

it('registers approved dormitory structure permission catalog keys without role grants', function (): void {
    seedDormitoryStructurePermissionCatalog();

    expect(Permission::query()->where('name', DormitoryStructurePermissionCatalog::VIEW)->exists())->toBeTrue()
        ->and(Permission::query()->where('name', DormitoryStructurePermissionCatalog::MANAGE)->exists())->toBeTrue()
        ->and(IdentityRoleSeeder::PERMISSIONS)->toContain(DormitoryStructurePermissionCatalog::VIEW)
        ->and(IdentityRoleSeeder::PERMISSIONS)->toContain(DormitoryStructurePermissionCatalog::MANAGE);
});

it('allows structure manage with the approved manage permission', function (): void {
    $created = withDormitoryStructureManageActor(
        fn () => app(DormitoryStructureMutationContract::class)->createDormitory(new CreateDormitoryData(
            code: 'AUTH-MANAGE-OK',
            name: 'Authorized Manage',
        )),
    );

    expect($created->id)->not->toBe('');
});

it('denies structure manage without the approved manage permission', function (): void {
    expect(fn () => withDormitoryStructureUnauthorizedActor(
        fn () => app(DormitoryStructureMutationContract::class)->createDormitory(new CreateDormitoryData(
            code: 'AUTH-MANAGE-DENY',
            name: 'Denied Manage',
        )),
    ))->toThrow(UnauthorizedDormitoryStructureAccessException::class);
});

it('allows structure view with the approved view permission', function (): void {
    $listed = withDormitoryStructureViewActor(
        fn () => app(DormitoryStructureReadContract::class)->listDormitories(),
    );

    expect($listed)->toBe([]);
});

it('denies structure view without the approved view permission', function (): void {
    expect(fn () => withDormitoryStructureUnauthorizedActor(
        fn () => app(DormitoryStructureReadContract::class)->listDormitories(),
    ))->toThrow(UnauthorizedDormitoryStructureAccessException::class);
});

it('does not treat structure manage as granting unresolved occupancy APIs', function (): void {
    expect(fn () => withDormitoryStructureManageActor(
        fn () => app(DormitoryStructureMutationContract::class)
            ->recordBedOccupancyStart('00000000-0000-7000-8000-000000000001'),
    ))->toThrow(UnauthorizedDormitoryStructureAccessException::class);
});
