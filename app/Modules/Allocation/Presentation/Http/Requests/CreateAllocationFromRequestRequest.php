<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Presentation\Http\Requests;

use App\Modules\Allocation\Presentation\Http\Requests\Concerns\ProhibitsMutationIdentitySpoofingFields;
use App\Modules\Allocation\Presentation\Http\Requests\Concerns\ValidatesRequestRouteIdForAllocation;
use Illuminate\Foundation\Http\FormRequest;

final class CreateAllocationFromRequestRequest extends FormRequest
{
    use ProhibitsMutationIdentitySpoofingFields;
    use ValidatesRequestRouteIdForAllocation;

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
            $this->requestRouteIdRules(),
            [
                'bedId' => ['nullable', 'uuid'],
            ],
            $this->identitySpoofingProhibitionRules(),
        );
    }
}
