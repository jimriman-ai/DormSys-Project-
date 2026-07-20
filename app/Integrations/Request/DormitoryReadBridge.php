<?php

declare(strict_types=1);

namespace App\Integrations\Request;

use App\Modules\Dormitory\Application\Contracts\DormitoryStructureReadContract;
use App\Modules\Dormitory\Application\DTOs\DormitorySummaryData;
use App\Modules\Request\Application\Contracts\DormitoryReadContract;
use App\Modules\Request\Application\DTOs\DormitorySiteSummaryDTO;

/**
 * Request → Dormitory site-existence bridge (Spec04 Phase 4 live wiring).
 *
 * Lives in app/Integrations per integration-layer-policy — not in Request Infrastructure.
 */
final class DormitoryReadBridge implements DormitoryReadContract
{
    public function __construct(
        private readonly DormitoryStructureReadContract $dormitories,
    ) {}

    public function siteExists(string $dormitorySiteId): bool
    {
        return $this->dormitories->getDormitoryDetail($dormitorySiteId) !== null;
    }

    /**
     * @return list<DormitorySiteSummaryDTO>
     */
    public function listSites(): array
    {
        return array_values(array_map(
            static fn (DormitorySummaryData $site): DormitorySiteSummaryDTO => new DormitorySiteSummaryDTO(
                id: $site->id,
                code: $site->code,
                name: $site->name,
                status: $site->status,
            ),
            $this->dormitories->listDormitories(),
        ));
    }
}
