<?php

declare(strict_types=1);

namespace App\Modules\Request\Infrastructure\Repositories;

use App\Modules\Request\Application\Contracts\RequestApprovalRepositoryContract;
use App\Modules\Request\Domain\Entities\RequestApproval;
use App\Modules\Request\Domain\ValueObjects\ApproverReferenceId;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use App\Modules\Request\Infrastructure\Persistence\Models\RequestApprovalModel;
use Illuminate\Support\Carbon;

class RequestApprovalRepository implements RequestApprovalRepositoryContract
{
    public function append(RequestApproval $approval): RequestApproval
    {
        $model = new RequestApprovalModel([
            'request_id' => $approval->requestId->value,
            'stage' => $approval->stage,
            'decision' => $approval->decision,
            'approver_id' => $approval->approverId->value,
            'reason' => $approval->reason,
            'decided_at' => $approval->decidedAt->format('Y-m-d H:i:s'),
            'created_at' => $approval->decidedAt->format('Y-m-d H:i:s'),
        ]);
        $model->save();

        return $this->toDomain($model);
    }

    public function countForRequest(RequestId $requestId): int
    {
        return RequestApprovalModel::query()
            ->where('request_id', $requestId->value)
            ->count();
    }

    public function listForRequest(RequestId $requestId): array
    {
        return array_values(
            RequestApprovalModel::query()
                ->where('request_id', $requestId->value)
                ->orderBy('decided_at')
                ->get()
                ->map(fn (RequestApprovalModel $model): RequestApproval => $this->toDomain($model))
                ->all(),
        );
    }

    private function toDomain(RequestApprovalModel $model): RequestApproval
    {
        return new RequestApproval(
            id: (string) $model->id,
            requestId: RequestId::fromString($model->request_id),
            stage: $model->stage,
            decision: $model->decision,
            approverId: ApproverReferenceId::fromString($model->approver_id),
            reason: $model->reason,
            decidedAt: Carbon::instance($model->decided_at)->utc()->toDateTimeImmutable(),
        );
    }
}
