<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Identity\Domain\PlatformRoles;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class IdentityRoleSeeder extends Seeder
{
    public const string ROLE_SYSTEM_ADMINISTRATOR = PlatformRoles::SYSTEM_ADMINISTRATOR;

    /**
     * @var list<string>
     */
    public const array PERMISSIONS = [
        'identity.users.manage',
        'identity.users.view',
        'identity.roles.manage',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'web';

        foreach (self::PERMISSIONS as $permissionName) {
            Permission::findOrCreate($permissionName, $guard);
        }

        $role = Role::findOrCreate(self::ROLE_SYSTEM_ADMINISTRATOR, $guard);
        $role->syncPermissions(self::PERMISSIONS);
    }
}
