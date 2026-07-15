<?php

declare(strict_types=1);

namespace App\Support\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Spatie\Permission\Models\Role;

/**
 * Shared identity-guard Spatie role assertion (SEC-G-01 / SEC-G-02).
 */
final class IdentityRoleGuard
{
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
}
