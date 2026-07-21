<?php

declare(strict_types=1);

namespace App\Modules\Workflow\Application\Support;

/** Stable system actor for auto-approval orchestration audit (not product user). */
final class WorkflowSystemActor
{
    public const string IDENTITY_ID = '00000000-0000-7000-8000-000000000001';
}
