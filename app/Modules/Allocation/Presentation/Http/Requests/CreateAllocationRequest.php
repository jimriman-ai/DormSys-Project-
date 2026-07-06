<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Presentation\Http\Requests;

use App\Modules\Allocation\Presentation\Http\Requests\Concerns\ProhibitsMutationIdentitySpoofingFields;
use Illuminate\Foundation\Http\FormRequest;

final class CreateAllocationRequest extends FormRequest
{
    use ProhibitsMutationIdentitySpoofingFields;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(
            [
                'personId' => ['required', 'uuid'],
                'bedId' => ['required', 'uuid'],
                'startDate' => ['required', 'date_format:Y-m-d'],
                'endDate' => ['required', 'date_format:Y-m-d', 'after:startDate'],
            ],
            $this->identitySpoofingProhibitionRules(),
        );
    }
}
