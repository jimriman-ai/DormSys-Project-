<?php

declare(strict_types=1);

use App\Application\Mutation\Exceptions\UnauthorizedMutationException;
use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\Identity\Application\Services\CreateRoleAction;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(MutationPrincipalContextHolder::class)->clear();
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);
});

it('enforces assertManageRoles in application layer without HTTP middleware', function (): void {
    $actorId = createActiveMutationActorId('App Layer No Perm');

    expect(fn () => mutationActingAs(
        $actorId,
        fn () => app(CreateRoleAction::class)->execute('ShouldFailRole'),
    ))->toThrow(UnauthorizedMutationException::class, 'identity.roles.manage');
});

it('allows create role via application when actor holds identity.roles.manage', function (): void {
    $actorId = createActiveMutationActorId('App Layer With Perm');
    grantIdentityRolesManagePermission($actorId);

    $role = mutationActingAs(
        $actorId,
        fn () => app(CreateRoleAction::class)->execute('AppLayerCreatedRole'),
    );

    expect($role->name)->toBe('AppLayerCreatedRole')
        ->and($role->guardName)->toBe('web');
});
