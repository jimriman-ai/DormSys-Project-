<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Contracts;

use App\Modules\Request\Domain\Entities\Request;
use App\Modules\Request\Domain\ValueObjects\RequestCode;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use Illuminate\Support\Collection;

interface RequestRepositoryContract
{
    public function save(Request $request): Request;

    public function findById(RequestId $id): ?Request;

    public function findByCode(RequestCode $code): ?Request;

    public function nextDailySequenceForUtcDate(string $datePart): int;

    /**
     * Stage-1 pending queue for the assigned approver (OQ-REQ-06 Option A).
     *
     * Status pending_department_manager AND assigned_stage1_approver_identity_id match.
     *
     * @return Collection<int, Request>
     */
    public function listPendingStage1(string $approverIdentityId): Collection;
}
