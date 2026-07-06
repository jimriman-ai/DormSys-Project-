<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Presentation\Http\Requests\Concerns;

trait ValidatesCheckInAllocationRouteId
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'allocationId' => $this->route('allocationId'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function checkInAllocationRouteIdRules(): array
    {
        return [
            'allocationId' => ['required', 'uuid'],
        ];
    }
}
