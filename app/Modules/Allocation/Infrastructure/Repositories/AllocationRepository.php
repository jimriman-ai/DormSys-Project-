<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Infrastructure\Repositories;

use App\Modules\Allocation\Application\Contracts\AllocationRepositoryContract;
use App\Modules\Allocation\Domain\Enums\AllocationStatus;
use App\Modules\Allocation\Domain\Exceptions\AllocationNotFoundException;
use App\Modules\Allocation\Domain\Exceptions\AllocationOverlapException;
use App\Modules\Allocation\Domain\Models\Allocation;
use App\Modules\Allocation\Domain\Models\AllocationItem;
use App\Modules\Allocation\Domain\ValueObjects\AllocationId;
use App\Modules\Allocation\Domain\ValueObjects\AllocationItemId;
use App\Modules\Allocation\Domain\ValueObjects\DateRange;
use App\Modules\Allocation\Domain\ValueObjects\PersonAllocationRef;
use App\Modules\Allocation\Infrastructure\Persistence\Models\AllocationItemModel;
use App\Modules\Allocation\Infrastructure\Persistence\Models\AllocationModel;
use DateTimeImmutable;
use Illuminate\Database\QueryException;

class AllocationRepository implements AllocationRepositoryContract
{
    public function save(Allocation $allocation): Allocation
    {
        try {
            if ($allocation->id === null) {
                return $this->insert($allocation);
            }

            return $this->update($allocation);
        } catch (QueryException $exception) {
            if ($this->isOverlapViolation($exception)) {
                throw new AllocationOverlapException(
                    'An overlapping allocation already exists for this person.',
                    previous: $exception,
                );
            }

            throw $exception;
        }
    }

    public function findById(AllocationId $id): ?Allocation
    {
        $model = AllocationModel::query()
            ->with('items')
            ->find($id->value);

        return $model === null ? null : $this->toDomain($model);
    }

    public function findActiveByPersonId(PersonAllocationRef $personId): array
    {
        $allocations = [];

        foreach (
            AllocationModel::query()
                ->with('items')
                ->where('person_id', $personId->value)
                ->where('status', AllocationStatus::Active)
                ->get() as $model
        ) {
            $allocations[] = $this->toDomain($model);
        }

        return $allocations;
    }

    private function insert(Allocation $allocation): Allocation
    {
        $model = new AllocationModel([
            'person_id' => $allocation->personId->value,
            'bed_id' => $allocation->bedId,
            'date_range' => $allocation->dateRange->toPostgresDaterange(),
            'method' => $allocation->method,
            'status' => $allocation->status,
            'source_request_id' => $allocation->sourceRequestId,
            'source_lottery_result_id' => $allocation->sourceLotteryResultId,
            'released_at' => $allocation->releasedAt,
            'release_reason' => $allocation->releaseReason,
        ]);
        $model->save();

        $items = $this->syncItems($model, $allocation->items);

        return $this->toDomain($model->load('items'), $items);
    }

    private function update(Allocation $allocation): Allocation
    {
        $model = AllocationModel::query()->find($allocation->requireId()->value);

        if ($model === null) {
            throw new AllocationNotFoundException('Allocation not found.');
        }

        $model->fill([
            'person_id' => $allocation->personId->value,
            'bed_id' => $allocation->bedId,
            'date_range' => $allocation->dateRange->toPostgresDaterange(),
            'method' => $allocation->method,
            'status' => $allocation->status,
            'source_request_id' => $allocation->sourceRequestId,
            'source_lottery_result_id' => $allocation->sourceLotteryResultId,
            'released_at' => $allocation->releasedAt,
            'release_reason' => $allocation->releaseReason,
        ]);
        $model->save();

        $items = $this->syncItems($model, $allocation->items);

        return $this->toDomain($model->load('items'), $items);
    }

    /**
     * @param  list<AllocationItem>  $items
     * @return list<AllocationItem>
     */
    private function syncItems(AllocationModel $model, array $items): array
    {
        $persisted = [];

        foreach ($items as $item) {
            if ($item->id === null) {
                $itemModel = new AllocationItemModel([
                    'allocation_id' => $model->getId(),
                    'bed_id' => $item->bedId,
                    'sequence' => $item->sequence,
                ]);
                $itemModel->save();
                $persisted[] = new AllocationItem(
                    id: AllocationItemId::fromString($itemModel->getId()),
                    allocationId: AllocationId::fromString($model->getId()),
                    bedId: $item->bedId,
                    sequence: $item->sequence,
                );

                continue;
            }

            $itemModel = AllocationItemModel::query()->find($item->requireId()->value);

            if ($itemModel === null) {
                throw new AllocationNotFoundException('Allocation item not found.');
            }

            $itemModel->fill([
                'bed_id' => $item->bedId,
                'sequence' => $item->sequence,
            ]);
            $itemModel->save();
            $persisted[] = $item;
        }

        return $persisted;
    }

    /**
     * @param  ?list<AllocationItem>  $items
     */
    private function toDomain(AllocationModel $model, ?array $items = null): Allocation
    {
        if ($items !== null) {
            $domainItems = $items;
        } else {
            $domainItems = [];

            foreach ($model->items as $itemModel) {
                $domainItems[] = new AllocationItem(
                    id: AllocationItemId::fromString($itemModel->getId()),
                    allocationId: AllocationId::fromString($model->getId()),
                    bedId: $itemModel->bed_id,
                    sequence: (int) $itemModel->sequence,
                );
            }
        }

        return new Allocation(
            id: AllocationId::fromString($model->getId()),
            personId: PersonAllocationRef::fromString($model->person_id),
            bedId: $model->bed_id,
            dateRange: DateRange::fromPostgresDaterange($model->date_range),
            method: $model->method,
            status: $model->status,
            sourceRequestId: $model->source_request_id,
            sourceLotteryResultId: $model->source_lottery_result_id,
            releasedAt: $model->released_at !== null
                ? DateTimeImmutable::createFromInterface($model->released_at)
                : null,
            releaseReason: $model->release_reason,
            items: $domainItems,
        );
    }

    private function isOverlapViolation(QueryException $exception): bool
    {
        return str_contains($exception->getMessage(), 'allocations_person_date_range_exclusion');
    }
}
