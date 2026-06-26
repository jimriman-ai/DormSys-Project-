<?php

declare(strict_types=1);

use PHPUnit\Framework\AssertionFailedError;
use Tests\Architecture\Fixtures\CanaryViolatingDomain;

arch('canary fixture proves layer rules detect violations')
    ->expect(CanaryViolatingDomain::class)
    ->not->toExtend('Illuminate\Database\Eloquent\Model')
    /** @phpstan-ignore method.notFound */
    ->throws(AssertionFailedError::class);
