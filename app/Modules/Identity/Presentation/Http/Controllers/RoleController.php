<?php

declare(strict_types=1);

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Modules\Identity\Application\Services\CreateRoleAction;
use App\Modules\Identity\Application\Services\DeleteRoleAction;
use App\Modules\Identity\Application\Services\ListRolesAction;
use App\Modules\Identity\Application\Services\RenameRoleAction;
use App\Modules\Identity\Presentation\Http\Requests\StoreRoleRequest;
use App\Modules\Identity\Presentation\Http\Requests\UpdateRoleRequest;
use App\Modules\Identity\Presentation\Http\Resources\RoleDetailResource;
use App\Modules\Identity\Presentation\Http\Resources\RoleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

final class RoleController
{
    public function __construct(
        private readonly ListRolesAction $listRoles,
        private readonly CreateRoleAction $createRole,
        private readonly RenameRoleAction $renameRole,
        private readonly DeleteRoleAction $deleteRole,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        return RoleResource::collection($this->listRoles->execute());
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = $this->createRole->execute($request->roleName());

        return (new RoleDetailResource($role))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdateRoleRequest $request, int $role): RoleDetailResource
    {
        return new RoleDetailResource(
            $this->renameRole->execute($role, $request->roleName()),
        );
    }

    public function destroy(int $role): JsonResponse
    {
        $this->deleteRole->execute($role);

        return response()->json([
            'success' => true,
        ]);
    }
}
