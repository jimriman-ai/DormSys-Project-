<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Services;

use App\Modules\Request\Application\Contracts\RequestRepositoryContract;
use App\Modules\Request\Domain\Entities\Request;
use Illuminate\Support\Collection;

/**
 * [PERMIT-ID: IMPL-PERMIT-03] Stage-1 pending queue read.
 *
 * Identity resolution and role gate live at the HTTP/Livewire boundary (DG-REQ-01 Option A amended / WP-DEBT-03).
 * Keeps Presentation off RequestRepositoryContract.
 */
final class ListPendingStage1RequestsAction
{
    public function __construct(
        private readonly RequestRepositoryContract $requests,
    ) {}

    /**
     * @return Collection<int, Request>
     */
    public function execute(string $approverIdentityId): Collection
    {
        return $this->requests->listPendingStage1($approverIdentityId);
    }
}
