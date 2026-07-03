<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Services;

use App\Modules\Audit\Application\DTOs\AuditHistoryItemDto;

final class ProjectionCursorPositionSelector
{
    /**
     * @param  list<AuditHistoryItemDto>  $items
     */
    public function selectLatest(array $items): ?AuditHistoryItemDto
    {
        if ($items === []) {
            return null;
        }

        $latest = $items[0];

        foreach ($items as $item) {
            if ($item->occurredAt > $latest->occurredAt) {
                $latest = $item;
            }
        }

        return $latest;
    }
}
