<?php

declare(strict_types=1);

use App\Modules\Identity\Application\Services\AssignRoleToUserAction;
use App\Modules\Identity\Application\Services\CreateUserAction;
use App\Modules\Identity\Application\Services\RevokeRoleFromUserAction;
use App\Modules\Identity\Domain\Exceptions\RoleNotFoundException;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Support\Facades\Artisan;

it('assigns role and grants permissions then revokes cleanly', function (): void {
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);

    $user = app(CreateUserAction::class)->execute('Role Test User', 'role@example.com');

    app(AssignRoleToUserAction::class)->execute(
        $user->requireId(),
        IdentityRoleSeeder::ROLE_SYSTEM_ADMINISTRATOR,
    );

    $model = UserModel::query()->findOrFail($user->requireId()->value);

    expect($model->hasPermissionTo('identity.users.manage'))->toBeTrue();

    app(RevokeRoleFromUserAction::class)->execute(
        $user->requireId(),
        IdentityRoleSeeder::ROLE_SYSTEM_ADMINISTRATOR,
    );

    $model->refresh();

    expect($model->hasPermissionTo('identity.users.manage'))->toBeFalse();
});

it('fails when assigning a non existent role', function (): void {
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);

    $user = app(CreateUserAction::class)->execute('No Role User', 'norole@example.com');

    expect(fn () => app(AssignRoleToUserAction::class)->execute($user->requireId(), 'NonExistentRole'))
        ->toThrow(RoleNotFoundException::class);
});
