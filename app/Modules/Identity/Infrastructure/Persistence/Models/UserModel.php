<?php

declare(strict_types=1);

namespace App\Modules\Identity\Infrastructure\Persistence\Models;

use App\Modules\Identity\Domain\Enums\UserStatus;
use App\Modules\Identity\Domain\PlatformRoles;
use App\Support\Models\BaseModel;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Spatie\Permission\Traits\HasRoles;

/**
 * Persistence adapter for Identity users (spec02).
 *
 * Implements {@see AuthenticatableContract} and {@see AuthorizableContract} so Spatie RBAC,
 * Laravel Gate/Policy (Q-DBT-1-AUTH Option B), and feature tests can resolve an authenticated
 * principal via `api` / `identity` guards (`actingAs`).
 * Password-based login is explicitly out of scope (OA-02-01); credentials
 * belong to {@see \App\Models\User} on the default `web` guard.
 *
 * @property string $id
 * @property UserStatus $status
 * @property string $display_name
 * @property string|null $email
 */
class UserModel extends BaseModel implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable;
    use Authorizable;
    use HasRoles;

    protected $table = 'identity_users';

    /**
     * Spatie multi-guard resolution (H-01 Option B).
     *
     * Accepts BOTH Auth guards that may authenticate this model for RBAC:
     * - web: historical Spatie role/permission rows (IdentityRoleSeeder)
     * - identity: dormitory-admin-ui Auth guard + identity-scoped roles
     *
     * Spatie\Permission\Guard::getNames() collects array $guard_name
     * (vendor/spatie/laravel-permission/src/Guard.php L19–37, package 8.0.0).
     * Do not set a single-string $guard_name or a string-returning guardName().
     *
     * @var list<string>
     */
    protected array $guard_name = ['web', 'identity'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'status',
        'display_name',
        'email',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'status' => UserStatus::class,
        ]);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', UserStatus::Active);
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function isSystemAdministrator(): bool
    {
        return $this->hasRole(PlatformRoles::SYSTEM_ADMINISTRATOR);
    }

    public function getAuthPassword(): string
    {
        throw new \LogicException(
            'Identity users do not support password-based authentication. Use App\Models\User on the web guard for credential login.',
        );
    }

    public function getRememberTokenName(): string
    {
        return '';
    }
}
