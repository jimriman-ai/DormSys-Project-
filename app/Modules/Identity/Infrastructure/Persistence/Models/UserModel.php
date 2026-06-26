<?php

declare(strict_types=1);

namespace App\Modules\Identity\Infrastructure\Persistence\Models;

use App\Modules\Identity\Domain\Enums\UserStatus;
use App\Modules\Identity\Domain\PlatformRoles;
use App\Support\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property string $id
 * @property UserStatus $status
 * @property string $display_name
 * @property string|null $email
 */
class UserModel extends BaseModel
{
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
}
