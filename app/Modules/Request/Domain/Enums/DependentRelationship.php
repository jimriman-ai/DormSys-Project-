<?php

declare(strict_types=1);

namespace App\Modules\Request\Domain\Enums;

enum DependentRelationship: string
{
    case Spouse = 'spouse';
    case Child = 'child';
    case Parent = 'parent';
    case Other = 'other';
}
