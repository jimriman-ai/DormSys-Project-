<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Authorization;

/**
 * Spec02-owned catalog keys for Dormitory structure authorization binding.
 *
 * Locked by human vocabulary decision — do not rename or expand here.
 */
final class DormitoryStructurePermissionCatalog
{
    public const string VIEW = 'dormitory.structure.view';

    public const string MANAGE = 'dormitory.structure.manage';

    private function __construct() {}
}
