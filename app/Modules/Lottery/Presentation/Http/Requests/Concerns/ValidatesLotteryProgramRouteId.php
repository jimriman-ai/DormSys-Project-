<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Presentation\Http\Requests\Concerns;

trait ValidatesLotteryProgramRouteId
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'programId' => $this->route('programId'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function lotteryProgramRouteIdRules(): array
    {
        return [
            'programId' => ['required', 'uuid'],
        ];
    }
}
