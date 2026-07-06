<?php

declare(strict_types=1);

use App\Modules\Identity\Domain\Exceptions\RoleNotFoundException;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Support\Facades\Artisan;

it('assigns role and grants permissions then revokes cleanly', function (): void {
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);

    $user = createIdentityUserThroughMutation('Role Test User', 'role@example.com');

    assignRoleThroughMutation(
        $user->requireId(),
        IdentityRoleSeeder::ROLE_SYSTEM_ADMINISTRATOR,
    );

    $model = UserModel::query()->findOrFail($user->requireId()->value);

    expect($model->hasPermissionTo('identity.users.manage'))->toBeTrue();

    revokeRoleFromUserThroughMutation(
        $user->requireId(),
        IdentityRoleSeeder::ROLE_SYSTEM_ADMINISTRATOR,
    );

    $model->refresh();

    expect($model->hasPermissionTo('identity.users.manage'))->toBeFalse();
});

it('fails when assigning a non existent role', function (): void {
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);

    $user = createIdentityUserThroughMutation('No Role User', 'norole@example.com');

    expect(fn () => assignRoleThroughMutation($user->requireId(), 'NonExistentRole'))
        ->toThrow(RoleNotFoundException::class);
});
