<?php

declare(strict_types=1);

namespace App\Modules\Request\Infrastructure\Repositories;

use App\Modules\Request\Application\Contracts\DependentSnapshotRepositoryContract;
use App\Modules\Request\Domain\Entities\DependentSnapshot;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use App\Modules\Request\Infrastructure\Persistence\Models\RequestDependentSnapshotModel;
use DateTimeImmutable;
use DateTimeZone;

class DependentSnapshotRepository implements DependentSnapshotRepositoryContract
{
    public function append(DependentSnapshot $snapshot): DependentSnapshot
    {
        $model = new RequestDependentSnapshotModel([
            'request_id' => $snapshot->requestId->value,
            'source_dependent_id' => $snapshot->sourceDependentId,
            'first_name' => $snapshot->firstName,
            'last_name' => $snapshot->lastName,
            'relationship' => $snapshot->relationship,
            'national_code' => $snapshot->nationalCode,
            'captured_at' => $snapshot->capturedAt->format('Y-m-d H:i:s'),
            'created_at' => $snapshot->capturedAt->format('Y-m-d H:i:s'),
        ]);
        $model->save();

        return $this->toDomain($model);
    }

    public function listForRequest(RequestId $requestId): array
    {
        return array_values(
            RequestDependentSnapshotModel::query()
                ->where('request_id', $requestId->value)
                ->orderBy('created_at')
                ->get()
                ->map(fn (RequestDependentSnapshotModel $model): DependentSnapshot => $this->toDomain($model))
                ->all(),
        );
    }

    private function toDomain(RequestDependentSnapshotModel $model): DependentSnapshot
    {
        return new DependentSnapshot(
            id: (string) $model->id,
            requestId: RequestId::fromString($model->request_id),
            sourceDependentId: $model->source_dependent_id,
            firstName: $model->first_name,
            lastName: $model->last_name,
            relationship: $model->relationship,
            nationalCode: $model->national_code,
            capturedAt: new DateTimeImmutable($model->captured_at->format('Y-m-d H:i:s'), new DateTimeZone('UTC')),
        );
    }
}
