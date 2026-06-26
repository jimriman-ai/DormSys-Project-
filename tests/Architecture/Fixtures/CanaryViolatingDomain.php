<?php

declare(strict_types=1);

namespace Tests\Architecture\Fixtures;

use Illuminate\Database\Eloquent\Model;

/**
 * Intentional architecture violation used only by ArchTestCanaryTest.
 */
final class CanaryViolatingDomain extends Model
{
    protected $table = 'architecture_canary_violations';
}
