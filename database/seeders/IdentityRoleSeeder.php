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

    public const string PERMISSION_IDENTITY_USERS_MANAGE = 'identity.users.manage';

    public const string PERMISSION_IDENTITY_USERS_VIEW = 'identity.users.view';

    /** Actor permission for AssignRoleToUserAction (D-L7-1); distinct from capability identity.role.assign. */
    public const string PERMISSION_IDENTITY_ROLES_MANAGE = 'identity.roles.manage';

    public const string PERMISSION_EMPLOYEE_RECORDS_READ = 'employee_records.read';

    public const string PERMISSION_EMPLOYEE_RECORDS_EDIT = 'employee_records.edit';

    public const string PERMISSION_DORMITORY_STRUCTURE_VIEW = DormitoryStructurePermissionCatalog::VIEW;

    public const string PERMISSION_DORMITORY_STRUCTURE_MANAGE = DormitoryStructurePermissionCatalog::MANAGE;

    /**
     * @var list<string>
     */
    public const array PERMISSIONS = [
        self::PERMISSION_IDENTITY_USERS_MANAGE,
        self::PERMISSION_IDENTITY_USERS_VIEW,
        self::PERMISSION_IDENTITY_ROLES_MANAGE,
        self::PERMISSION_AUDIT_READ,
        self::PERMISSION_EMPLOYEE_RECORDS_READ,
        self::PERMISSION_EMPLOYEE_RECORDS_EDIT,
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

        // Pre-existing Spec02 mapping (unchanged by D-L7-1). D-L7-1 does not authorize
        // new production role grants; HRMgr / Administrator do not receive roles.manage here.
        $systemAdministrator = Role::findOrCreate(self::ROLE_SYSTEM_ADMINISTRATOR, $guard);
        $systemAdministrator->syncPermissions([
            self::PERMISSION_IDENTITY_USERS_MANAGE,
            self::PERMISSION_IDENTITY_USERS_VIEW,
            self::PERMISSION_IDENTITY_ROLES_MANAGE,
        ]);

        foreach (self::AUDIT_READ_ROLES as $roleName) {
            $role = Role::findOrCreate($roleName, $guard);
            $role->givePermissionTo(self::PERMISSION_AUDIT_READ);
        }

        $hrMgr = Role::findOrCreate(self::ROLE_HR_MGR, $guard);
        $hrMgr->givePermissionTo([
            self::PERMISSION_EMPLOYEE_RECORDS_READ,
            self::PERMISSION_EMPLOYEE_RECORDS_EDIT,
        ]);
    }
}
