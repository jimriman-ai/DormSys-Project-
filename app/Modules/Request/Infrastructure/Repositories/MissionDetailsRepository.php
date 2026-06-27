<?php

declare(strict_types=1);

namespace App\Modules\Request\Infrastructure\Repositories;

use App\Modules\Request\Application\Contracts\MissionDetailsRepositoryContract;
use App\Modules\Request\Domain\Entities\MissionDetails;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use App\Modules\Request\Infrastructure\Persistence\Models\RequestMissionDetailsModel;
use DateTimeImmutable;
use DateTimeZone;

class MissionDetailsRepository implements MissionDetailsRepositoryContract
{
    public function save(MissionDetails $details): MissionDetails
    {
        $createdAt = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        $model = new RequestMissionDetailsModel([
            'request_id' => $details->requestId->value,
            'mission_document_url' => $details->missionDocumentUrl,
            'description' => $details->description,
            'created_at' => $createdAt->format('Y-m-d H:i:s'),
        ]);
        $model->save();

        return $details;
    }

    public function findForRequest(RequestId $requestId): ?MissionDetails
    {
        $model = RequestMissionDetailsModel::query()->find($requestId->value);

        if ($model === null) {
            return null;
        }

        return new MissionDetails(
            requestId: RequestId::fromString($model->request_id),
            description: $model->description,
            missionDocumentUrl: $model->mission_document_url,
        );
    }
}
