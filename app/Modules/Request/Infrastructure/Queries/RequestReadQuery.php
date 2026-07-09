<?php

declare(strict_types=1);

namespace App\Modules\Request\Infrastructure\Queries;

use App\Modules\Request\Application\Contracts\Internal\RequestReadQueryPort;
use App\Modules\Request\Application\DTOs\EmployeeRequestListQueryDTO;
use App\Modules\Request\Application\DTOs\PaginatedRequestSummaryListDTO;
use App\Modules\Request\Application\DTOs\RequestSummaryDTO;
use App\Modules\Request\Domain\Enums\RequestType;
use App\Modules\Request\Application\DTOs\RequestEmployeeListFilterOptions;
use App\Modules\Request\Domain\States\ApprovedState;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use App\Modules\Request\Infrastructure\Persistence\Models\RequestModel;
use Illuminate\Database\Eloquent\Builder;
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

    public function listByEmployee(string $employeeId): array
    {
        return $this->mapSummaries(
            RequestModel::query()
                ->where('employee_id', $employeeId)
                ->orderByDesc('created_at')
                ->get(),
        );
    }

    public function listByEmployeePaginated(EmployeeRequestListQueryDTO $query): PaginatedRequestSummaryListDTO
    {
        $builder = $this->employeeListQueryBuilder($query->employeeId, $query->status);

        $sortColumn = $this->resolveSortColumn($query->sortField);
        $direction = $query->sortDirection === 'asc' ? 'asc' : 'desc';

        $builder->orderBy($sortColumn, $direction);

        if ($sortColumn !== 'created_at') {
            $builder->orderByDesc('created_at');
        }

        $total = (clone $builder)->count();
        $perPage = $query->perPage;
        $lastPage = max((int) ceil($total / $perPage), 1);
        $page = min(max($query->page, 1), $lastPage);

        if ($total === 0) {
            $page = 1;
        }

        $models = $builder->forPage($page, $perPage)->get();

        return new PaginatedRequestSummaryListDTO(
            items: $this->mapSummaries($models),
            total: $total,
            currentPage: $page,
            perPage: $perPage,
            lastPage: $lastPage,
            statusOptions: RequestEmployeeListFilterOptions::statusValues(),
        );
    }

    /**
     * @return list<string>
     */
    public static function filterableStatusOptions(): array
    {
        return RequestEmployeeListFilterOptions::statusValues();
    }

    /**
     * @return Builder<RequestModel>
     */
    private function employeeListQueryBuilder(string $employeeId, ?string $status): Builder
    {
        $builder = RequestModel::query()->where('employee_id', $employeeId);

        if ($status !== null) {
            $builder->where('status', $status);
        }

        return $builder;
    }

    private function resolveSortColumn(string $sortField): string
    {
        return match ($sortField) {
            'submitted_at' => 'created_at',
            'code' => 'code',
            'status' => 'status',
            'check_in_date' => 'check_in_date',
            'check_out_date' => 'check_out_date',
            default => 'created_at',
        };
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
            cancelledAt: $model->cancelled_at?->format(DATE_ATOM),
            rejectionReason: $model->rejection_reason,
            memberCount: null,
            dependentCount: null,
        );
    }
}
