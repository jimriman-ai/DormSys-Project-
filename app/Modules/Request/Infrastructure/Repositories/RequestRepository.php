<?php

declare(strict_types=1);

namespace App\Modules\Request\Infrastructure\Repositories;

use App\Modules\Request\Application\Contracts\RequestRepositoryContract;
use App\Modules\Request\Domain\Entities\Request;
use App\Modules\Request\Domain\Exceptions\RequestNotFoundException;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use App\Modules\Request\Domain\ValueObjects\RequestCode;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use App\Modules\Request\Infrastructure\Persistence\Models\RequestModel;
use Illuminate\Support\Carbon;

class RequestRepository implements RequestRepositoryContract
{
    public function save(Request $request): Request
    {
        if ($request->id === null) {
            $model = new RequestModel([
                'code' => (string) $request->code,
                'employee_id' => $request->employeeId->value,
                'assigned_stage1_approver_identity_id' => $request->assignedStage1ApproverIdentityId,
                'dormitory_id' => $request->dormitoryId->value,
                'type' => $request->type,
                'check_in_date' => $request->checkInDate->format('Y-m-d'),
                'check_out_date' => $request->checkOutDate->format('Y-m-d'),
                'status' => $request->status,
                'submitted_at' => $request->submittedAt?->format('Y-m-d H:i:s'),
                'cancelled_at' => $request->cancelledAt?->format('Y-m-d H:i:s'),
                'rejection_reason' => $request->rejectionReason,
            ]);
            $model->save();

            return $this->toDomain($model);
        }

        $model = RequestModel::query()->find($request->requireId()->value);

        if ($model === null) {
            throw new RequestNotFoundException('Request not found.');
        }

        $model->fill([
            'code' => (string) $request->code,
            'employee_id' => $request->employeeId->value,
            'assigned_stage1_approver_identity_id' => $request->assignedStage1ApproverIdentityId,
            'dormitory_id' => $request->dormitoryId->value,
            'type' => $request->type,
            'check_in_date' => $request->checkInDate->format('Y-m-d'),
            'check_out_date' => $request->checkOutDate->format('Y-m-d'),
            'status' => $request->status,
            'submitted_at' => $request->submittedAt?->format('Y-m-d H:i:s'),
            'cancelled_at' => $request->cancelledAt?->format('Y-m-d H:i:s'),
            'rejection_reason' => $request->rejectionReason,
        ]);
        $model->save();

        return $this->toDomain($model);
    }

    public function findById(RequestId $id): ?Request
    {
        $model = RequestModel::query()->find($id->value);

        return $model === null ? null : $this->toDomain($model);
    }

    public function findByCode(RequestCode $code): ?Request
    {
        $model = RequestModel::query()
            ->where('code', (string) $code)
            ->first();

        return $model === null ? null : $this->toDomain($model);
    }

    public function nextDailySequenceForUtcDate(string $datePart): int
    {
        $prefix = 'REQ-'.$datePart.'-';
        $latestCode = RequestModel::query()
            ->where('code', 'like', $prefix.'%')
            ->orderByDesc('code')
            ->value('code');

        if ($latestCode === null) {
            return 1;
        }

        return RequestCode::fromString($latestCode)->sequence() + 1;
    }

    private function toDomain(RequestModel $model): Request
    {
        return new Request(
            id: RequestId::fromString($model->getId()),
            code: RequestCode::fromString($model->code),
            employeeId: EmployeeReferenceId::fromString($model->employee_id),
            dormitoryId: DormitorySiteId::fromString($model->dormitory_id),
            type: $model->type,
            checkInDate: Carbon::instance($model->check_in_date)->utc()->toDateTimeImmutable(),
            checkOutDate: Carbon::instance($model->check_out_date)->utc()->toDateTimeImmutable(),
            status: $model->status->getValue(),
            submittedAt: $model->submitted_at !== null
                ? Carbon::instance($model->submitted_at)->utc()->toDateTimeImmutable()
                : null,
            cancelledAt: $model->cancelled_at !== null
                ? Carbon::instance($model->cancelled_at)->utc()->toDateTimeImmutable()
                : null,
            rejectionReason: $model->rejection_reason,
            assignedStage1ApproverIdentityId: $model->assigned_stage1_approver_identity_id,
        );
    }
}
