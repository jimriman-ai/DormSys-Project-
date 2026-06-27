<?php

declare(strict_types=1);

namespace App\Modules\Request\Infrastructure\Repositories;

use App\Modules\Request\Application\Contracts\RequestMemberRepositoryContract;
use App\Modules\Request\Domain\Entities\RequestMember;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use App\Modules\Request\Infrastructure\Persistence\Models\RequestMemberModel;
use DateTimeImmutable;
use DateTimeZone;

class RequestMemberRepository implements RequestMemberRepositoryContract
{
    public function append(RequestMember $member): RequestMember
    {
        $createdAt = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        $model = new RequestMemberModel([
            'request_id' => $member->requestId->value,
            'employee_id' => $member->employeeId,
            'is_leader' => $member->isLeader,
            'created_at' => $createdAt->format('Y-m-d H:i:s'),
        ]);
        $model->save();

        return $this->toDomain($model);
    }

    public function listForRequest(RequestId $requestId): array
    {
        return array_values(
            RequestMemberModel::query()
                ->where('request_id', $requestId->value)
                ->orderBy('created_at')
                ->get()
                ->map(fn (RequestMemberModel $model): RequestMember => $this->toDomain($model))
                ->all(),
        );
    }

    private function toDomain(RequestMemberModel $model): RequestMember
    {
        return new RequestMember(
            id: (string) $model->id,
            requestId: RequestId::fromString($model->request_id),
            employeeId: $model->employee_id,
            isLeader: $model->is_leader,
        );
    }
}
