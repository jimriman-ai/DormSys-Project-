<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Application\Services;

use App\Modules\Reporting\Application\Contracts\Ports\ProjectionRefreshMaterializerPort;
use App\Modules\Reporting\Domain\Enums\ProjectionFamily;
use App\Modules\Reporting\Domain\Exceptions\UnsupportedProjectionFamilyException;

final class ProjectionRefreshMaterializerRegistry
{
    /**
     * @param  iterable<ProjectionRefreshMaterializerPort>  $materializers
     */
    public function __construct(
        private readonly iterable $materializers,
    ) {}

    public function resolve(ProjectionFamily $projectionFamily): ProjectionRefreshMaterializerPort
    {
        foreach ($this->materializers as $materializer) {
            if ($materializer->supports($projectionFamily)) {
                return $materializer;
            }
        }

        throw UnsupportedProjectionFamilyException::forFamily($projectionFamily);
    }
}
