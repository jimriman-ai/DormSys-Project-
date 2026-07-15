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
 * Implements {@see AuthenticatableContract} only so Spatie RBAC and feature tests
 * can resolve an authenticated principal via the `api` guard (`actingAs`).
 * Password-based login is explicitly out of scope (OA-02-01); credentials
 * belong to {@see \App\Models\User} on the default `web` guard.
 *
 * {@see AuthorizableContract} enables Spatie `permission:` middleware (`can` / `canAny`).
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

    public function guardName(): string
    {
        return 'web';
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
