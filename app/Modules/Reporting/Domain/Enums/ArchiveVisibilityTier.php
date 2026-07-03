<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Domain\Enums;

enum ArchiveVisibilityTier: string
{
    case ActiveOnly = 'active_only';
    case IncludeArchived = 'include_archived';
}
