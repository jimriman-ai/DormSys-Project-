<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Services;

use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Modules\Request\Application\Contracts\RequestRepositoryContract;
use App\Modules\Request\Domain\Entities\Request;
use App\Shared\Auth\IdentityRoleGuard;
use Illuminate\Support\Collection;

/**
 * [PERMIT-ID: IMPL-PERMIT-03] Stage-1 pending queue read — IdentityRoleGuard first, then repository list.
 *
 * Role: dormitory-manager (identity). Keeps Presentation off RequestRepositoryContract.
 */
final class ListPendingStage1RequestsAction
{
    public function __construct(
        private readonly RequestRepositoryContract $requests,
    ) {}

    /**
     * @return Collection<int, Request>
     */
    public function execute(): Collection
    {
        IdentityRoleGuard::assertDormitoryManager();

        $user = auth('identity')->user();
        assert($user instanceof UserModel);

        return $this->requests->listPendingStage1($user->getId());
    }
}
