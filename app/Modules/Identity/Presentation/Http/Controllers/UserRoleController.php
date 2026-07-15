<?php

declare(strict_types=1);

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Modules\Identity\Application\Services\ListRoleUsersAction;
use App\Modules\Identity\Application\Services\SyncUserRolesAction;
use App\Modules\Identity\Presentation\Http\Requests\SyncUserRolesRequest;
use Illuminate\Http\JsonResponse;

final class UserRoleController
{
    public function __construct(
        private readonly ListRoleUsersAction $listRoleUsers,
        private readonly SyncUserRolesAction $syncUserRoles,
    ) {}

    public function users(int $role): JsonResponse
    {
        $users = $this->listRoleUsers->execute($role);

        return response()->json([
            'success' => true,
            'data' => array_map(
                static fn ($user): array => [
                    'id' => $user->id,
                    'display_name' => $user->displayName,
                    'email' => $user->email,
                    'status' => $user->status,
                ],
                $users,
            ),
        ]);
    }

    public function sync(SyncUserRolesRequest $request, string $user): JsonResponse
    {
        $this->syncUserRoles->execute($user, $request->roleIds());

        return response()->json([
            'success' => true,
        ]);
    }
}
