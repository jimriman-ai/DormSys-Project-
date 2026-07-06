<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Presentation\Http\Requests\Concerns;

trait ValidatesRequestRouteIdForAllocation
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'requestId' => $this->route('requestId'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function requestRouteIdRules(): array
    {
        return [
            'requestId' => ['required', 'uuid'],
        ];
    }
}
