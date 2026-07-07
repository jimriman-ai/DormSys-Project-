<?php

declare(strict_types=1);

namespace App\Support\Presentation\Concerns;

use App\Support\Exceptions\DomainException;

trait HandlesUiMutationFeedback
{
    public ?string $actionError = null;

    protected function resetActionFeedback(): void
    {
        $this->actionError = null;
    }

    protected function flashSuccess(string $message): void
    {
        session()->flash('success', $message);
    }

    protected function captureMutationFailure(\Throwable $exception): void
    {
        if ($exception instanceof DomainException) {
            $this->actionError = $exception->getMessage();

            return;
        }

        throw $exception;
    }
}
