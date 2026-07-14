<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Identity\Application\Authorization\DormitoryStructurePermissionCatalog;
use App\Modules\Identity\Domain\PlatformRoles;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class IdentityRoleSeeder extends Seeder
{
    public const string ROLE_SYSTEM_ADMINISTRATOR = PlatformRoles::SYSTEM_ADMINISTRATOR;

    public const string ROLE_ADMINISTRATOR = 'Administrator';

    public const string ROLE_DORM_MGR = 'DormMgr';

    public const string ROLE_HR_MGR = 'HRMgr';

    public const string PERMISSION_AUDIT_READ = 'audit.read';

    public const string PERMISSION_STUDENT_RECORDS_READ = 'student_records.read';

    public const string PERMISSION_STUDENT_RECORDS_EDIT = 'student_records.edit';

    public const string PERMISSION_DORMITORY_STRUCTURE_VIEW = DormitoryStructurePermissionCatalog::VIEW;

    public const string PERMISSION_DORMITORY_STRUCTURE_MANAGE = DormitoryStructurePermissionCatalog::MANAGE;

    /**
     * @var list<string>
     */
    public const array PERMISSIONS = [
        'identity.users.manage',
        'identity.users.view',
        'identity.roles.manage',
        self::PERMISSION_AUDIT_READ,
        self::PERMISSION_STUDENT_RECORDS_READ,
        self::PERMISSION_STUDENT_RECORDS_EDIT,
        self::PERMISSION_DORMITORY_STRUCTURE_VIEW,
        self::PERMISSION_DORMITORY_STRUCTURE_MANAGE,
    ];

    /**
     * @var list<string>
     */
    public const array AUDIT_READ_ROLES = [
        self::ROLE_ADMINISTRATOR,
        self::ROLE_DORM_MGR,
        self::ROLE_HR_MGR,
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'web';

        foreach (self::PERMISSIONS as $permissionName) {
            Permission::findOrCreate($permissionName, $guard);
        }

        $systemAdministrator = Role::findOrCreate(self::ROLE_SYSTEM_ADMINISTRATOR, $guard);
        $systemAdministrator->syncPermissions([
            'identity.users.manage',
            'identity.users.view',
            'identity.roles.manage',
        ]);

        foreach (self::AUDIT_READ_ROLES as $roleName) {
            $role = Role::findOrCreate($roleName, $guard);
            $role->givePermissionTo(self::PERMISSION_AUDIT_READ);
        }

        $hrMgr = Role::findOrCreate(self::ROLE_HR_MGR, $guard);
        $hrMgr->givePermissionTo([
            self::PERMISSION_STUDENT_RECORDS_READ,
            self::PERMISSION_STUDENT_RECORDS_EDIT,
        ]);
    }
}