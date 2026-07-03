<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Domain\Enums;

enum ProjectionFamily: string
{
    case Correlation = 'correlation';
    case WindowAggregate = 'window_aggregate';
    case EntityCache = 'entity_cache';
    case ActorActivity = 'actor_activity';
}
