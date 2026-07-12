<?php

declare(strict_types=1);

/**
 * BT-05 — Employee supplier boundary (MVP + post-US3/US4 regression).
 * Covers all App\Modules\Employee namespaces including Dependent and Eligibility code.
 * Does not assert EmployeeRead (Phase 7 deferred at Spec03 close).
 */
arch('employee module does not import identity infrastructure')
    ->expect('App\Modules\Employee')
    ->not->toUse('App\Modules\Identity\Infrastructure\*');

arch('employee module does not import identity persistence models')
    ->expect('App\Modules\Employee')
    ->not->toUse('App\Modules\Identity\Infrastructure\Persistence\Models\UserModel');
