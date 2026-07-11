<?php

declare(strict_types=1);

namespace App\Integrations\Request;

use App\Modules\Dormitory\Application\Contracts\DormitoryStructureReadContract;
use App\Modules\Request\Application\Contracts\DormitoryReadContract;

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
}
