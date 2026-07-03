<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Services;

use App\Modules\Reporting\Application\DTOs\ReportingProvenanceDto;

final class ReportingProvenanceFactory
{
    /**
     * @param  array<string, mixed>  $normalizedFilters
     */
    public function forT0(array $normalizedFilters, bool $includeArchived): ReportingProvenanceDto
    {
        $normalizedFilters['includeArchived'] = $includeArchived;

        return new ReportingProvenanceDto(
            sourceTier: 'T0',
            refreshedAt: null,
            projectionVersion: null,
            includeArchived: $includeArchived,
            filterHash: hash('sha256', json_encode($normalizedFilters, JSON_THROW_ON_ERROR)),
        );
    }
}
