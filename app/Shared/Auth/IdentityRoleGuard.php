<?php

declare(strict_types=1);

namespace App\Shared\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Spatie\Permission\Models\Role;

/**
 * Shared identity-guard Spatie role assertion (SEC-G-01 / SEC-G-02).
 */
final class IdentityRoleGuard
{
    /**
     * Canonical identity-guard role for Stage-1 approver authorization path (DGAP-13 / Lead DGAP-09 scoped).
     */
    public const string ROLE_DORMITORY_MANAGER = 'dormitory-manager';

    /**
     * Morph type for identity_users (string only — Shared must not import Modules).
     */
    private const string IDENTITY_USER_MODEL_TYPE = 'App\\Modules\\Identity\\Infrastructure\\Persistence\\Models\\UserModel';

    private const string IDENTITY_USERS_TABLE = 'identity_users';

    private const string ACTIVE_STATUS = 'active';

    public static function userHasIdentityRole(Authenticatable $user, string ...$roles): bool
    {
        if ($roles === [] || ! $user instanceof Model) {
            return false;
        }

        $modelId = $user->getAuthIdentifier();
        $modelType = $user->getMorphClass();
        $pivot = (string) config('permission.table_names.model_has_roles');

        return Role::query()
            ->whereIn('name', $roles)
            ->where('guard_name', 'identity')
            ->whereExists(function (QueryBuilder $query) use ($pivot, $modelId, $modelType): void {
                $query->selectRaw('1')
                    ->from($pivot)
                    ->whereColumn($pivot.'.role_id', 'roles.id')
                    ->where($pivot.'.model_type', $modelType)
                    ->where($pivot.'.model_id', $modelId);
            })
            ->exists();
    }

    public static function assertIdentityRole(string $role): void
    {
        $user = auth('identity')->user();

        if ($user === null || ! self::userHasIdentityRole($user, $role)) {
            abort(403);
        }
    }

    /**
     * Stage-1 approver console / action gate (DGAP-13 scoped).
     */
    public static function assertDormitoryManager(): void
    {
        self::assertIdentityRole(self::ROLE_DORMITORY_MANAGER);
    }

    /**
     * [PERMIT-ID: IMPL-PERMIT-02] Resolve an active identity UUID holding the given identity-guard role.
     *
     * Uses Spatie role/pivot + identity_users.status. Does not use Auth::user().
     *
     * @return non-empty-string|null
     */
    public static function resolveActiveIdentityIdForRole(string $role): ?string
    {
        $pivot = (string) config('permission.table_names.model_has_roles');

        $identityId = Role::query()
            ->where('name', $role)
            ->where('guard_name', 'identity')
            ->getQuery()
            ->join($pivot, $pivot.'.role_id', '=', 'roles.id')
            ->join(self::IDENTITY_USERS_TABLE, self::IDENTITY_USERS_TABLE.'.id', '=', $pivot.'.model_id')
            ->where($pivot.'.model_type', self::IDENTITY_USER_MODEL_TYPE)
            ->where(self::IDENTITY_USERS_TABLE.'.status', self::ACTIVE_STATUS)
            ->orderBy(self::IDENTITY_USERS_TABLE.'.id')
            ->value(self::IDENTITY_USERS_TABLE.'.id');

        if (! is_string($identityId) || $identityId === '') {
            return null;
        }

        return $identityId;
    }
}
