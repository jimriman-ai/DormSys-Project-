<?php

declare(strict_types=1);

use App\Modules\Dormitory\Domain\Enums\ResourceStatus;
use App\Modules\Dormitory\Infrastructure\Persistence\Models\DormitoryModel;

/**
 * Persist a real dormitory row for Request feature flows that call siteExists().
 *
 * Also seeds a Spec04 vacant bed with the same UUID so lottery draw paths that
 * allocate against dormitory_id as bedId (ProposedAllocationConsumer) succeed
 * under live DormitoryAssignabilityReadBridge wiring.
 */
function createDormitorySiteForRequestTests(?string $code = null): string
{
    $suffix = substr(str_replace('.', '', uniqid('', true)), -8);

    $dormitory = DormitoryModel::query()->create([
        'code' => $code ?? 'REQ-SITE-'.$suffix,
        'name' => 'Request Test Dormitory '.$suffix,
        'status' => ResourceStatus::Available,
    ]);

    $dormitoryId = $dormitory->getId();
    createAssignableBedForAllocationTests(id: $dormitoryId);

    return $dormitoryId;
}
