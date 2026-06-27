<?php

declare(strict_types=1);

namespace App\Modules\Request\Infrastructure\Queries;

use App\Modules\Request\Application\Contracts\Internal\RequestReadQueryPort;
use App\Modules\Request\Application\DTOs\RequestSummaryDTO;
use App\Modules\Request\Domain\Enums\RequestType;
use App\Modules\Request\Domain\States\ApprovedState;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use App\Modules\Request\Infrastructure\Persistence\Models\RequestModel;
use Illuminate\Support\Collection;

final class RequestReadQuery implements RequestReadQueryPort
{
    public function exists(RequestId $id): bool
    {
        return RequestModel::query()
            ->whereKey($id->value)
            ->exists();
    }

    public function findSummaryById(RequestId $id): ?RequestSummaryDTO
    {
        $model = RequestModel::query()->find($id->value);

        if ($model === null) {
            return null;
        }

        return $this->toSummaryDto($model);
    }

    public function listApprovedByEmployee(string $employeeId): array
    {
        return $this->mapSummaries(
            RequestModel::query()
                ->where('employee_id', $employeeId)
                ->where('status', ApprovedState::$name)
                ->orderBy('submitted_at')
                ->get(),
        );
    }

    public function listApprovedByType(string $requestType): array
    {
        $type = RequestType::from($requestType);

        return $this->mapSummaries(
            RequestModel::query()
                ->where('type', $type)
                ->where('status', ApprovedState::$name)
                ->orderBy('submitted_at')
                ->get(),
        );
    }

    /**
     * @param  Collection<int, RequestModel>  $models
     * @return list<RequestSummaryDTO>
     */
    private function mapSummaries(Collection $models): array
    {
        return array_values(
            $models
                ->map(fn (RequestModel $model): RequestSummaryDTO => $this->toSummaryDto($model))
                ->all(),
        );
    }

    private function toSummaryDto(RequestModel $model): RequestSummaryDTO
    {
        return new RequestSummaryDTO(
            id: $model->getId(),
            code: $model->code,
            employeeId: $model->employee_id,
            dormitoryId: $model->dormitory_id,
            type: $model->type->value,
            status: $model->status->getValue(),
            checkInDate: $model->check_in_date->format('Y-m-d'),
            checkOutDate: $model->check_out_date->format('Y-m-d'),
            submittedAt: $model->submitted_at?->format(DATE_ATOM),
            memberCount: null,
            dependentCount: null,
        );
    }
}
