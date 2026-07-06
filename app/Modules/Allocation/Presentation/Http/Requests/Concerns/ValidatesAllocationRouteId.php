<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Presentation\Http\Requests\Concerns;

trait ValidatesAllocationRouteId
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
    protected function allocationRouteIdRules(): array
    {
        return [
            'allocationId' => ['required', 'uuid'],
        ];
    }
}
