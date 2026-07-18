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

    /** Additive identity-guard role for dormitory-admin-ui (G-B). */
    public const string ROLE_DORMITORY_MANAGER = 'dormitory-manager';

    /** Canonical identity-guard role for unit (room) manager dashboard (G-C). */
    public const string ROLE_DORMITORY_UNIT_MANAGER = 'dormitory-unit-manager';

    /** [PERMIT-ID: IMPL-PERMIT-01] §2.3 / OQ-AUTH-01 B — employee self-service. */
    public const string ROLE_EMPLOYEE = 'employee';

    /**
     * [PERMIT-ID: IMPL-PERMIT-01] §2.3 / OQ-AUTH-01 B — Stage-1 dormitory-manager approver console.
     *
     * @deprecated SB-D1=A (Sprint B): deprecated alias of {@see self::ROLE_DORMITORY_MANAGER}.
     *             String value remains `dormitory-manager` — do not treat as a distinct Spatie role.
     */
    public const string ROLE_DEPT_MGR = 'dormitory-manager';

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
        $registrar = app(PermissionRegistrar::class);
        $registrar->forgetCachedPermissions();

        // Spec02 RBAC remains on Auth guard `web`. Dual-guard UserModel also accepts
        // `identity` (D-G-12); mirror permission rows so Spatie lookups for either
        // guard_name never throw PermissionDoesNotExist mid-suite (G-E-R / B2).
        foreach (['web', 'identity'] as $permissionGuard) {
            foreach (self::PERMISSIONS as $permissionName) {
                Permission::findOrCreate($permissionName, $permissionGuard);
            }
        }

        $web = 'web';

        // Pre-existing Spec02 mapping (unchanged by D-L7-1). D-L7-1 does not authorize
        // new production role grants; HRMgr / Administrator do not receive roles.manage here.
        $systemAdministrator = Role::findOrCreate(self::ROLE_SYSTEM_ADMINISTRATOR, $web);
        $systemAdministrator->syncPermissions([
            Permission::findByName(self::PERMISSION_IDENTITY_USERS_MANAGE, $web),
            Permission::findByName(self::PERMISSION_IDENTITY_USERS_VIEW, $web),
            Permission::findByName(self::PERMISSION_IDENTITY_ROLES_MANAGE, $web),
        ]);

        foreach (self::AUDIT_READ_ROLES as $roleName) {
            $role = Role::findOrCreate($roleName, $web);
            $role->givePermissionTo(Permission::findByName(self::PERMISSION_AUDIT_READ, $web));
        }

        $hrMgr = Role::findOrCreate(self::ROLE_HR_MGR, $web);
        $hrMgr->givePermissionTo([
            Permission::findByName(self::PERMISSION_EMPLOYEE_RECORDS_READ, $web),
            Permission::findByName(self::PERMISSION_EMPLOYEE_RECORDS_EDIT, $web),
        ]);

        // G-B / Decision 3-A revised: additive roles on the identity Auth guard.
        // Does not rename or re-guard ROLE_DORM_MGR ('DormMgr' / web). No permission grants.
        Role::findOrCreate(self::ROLE_DORMITORY_MANAGER, 'identity');
        Role::findOrCreate(self::ROLE_DORMITORY_UNIT_MANAGER, 'identity');

        // [PERMIT-ID: IMPL-PERMIT-01] §2.3 / IMP-Q-06 A — Spec04 identity roles (no permission grants yet).
        Role::findOrCreate(self::ROLE_EMPLOYEE, 'identity');
        Role::findOrCreate(self::ROLE_DEPT_MGR, 'identity');

        $registrar->forgetCachedPermissions();
    }
}
