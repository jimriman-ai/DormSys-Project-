<?php

declare(strict_types=1);

namespace App\Modules\Identity\Presentation\Http\Resources;

use App\Modules\Identity\Application\DTOs\RoleSummaryDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin RoleSummaryDTO
 */
final class RoleDetailResource extends JsonResource
{
    /**
     * @return array{id: int, name: string, guard_name: string, users_count: int}
     */
    public function toArray(Request $request): array
    {
        /** @var RoleSummaryDTO $role */
        $role = $this->resource;

        return [
            'id' => $role->id,
            'name' => $role->name,
            'guard_name' => $role->guardName,
            'users_count' => $role->usersCount,
        ];
    }
}
