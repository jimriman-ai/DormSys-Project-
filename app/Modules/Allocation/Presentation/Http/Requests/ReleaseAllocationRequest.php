<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Presentation\Http\Requests;

use App\Modules\Allocation\Presentation\Http\Requests\Concerns\ProhibitsMutationIdentitySpoofingFields;
use App\Modules\Allocation\Presentation\Http\Requests\Concerns\ValidatesAllocationRouteId;
use Illuminate\Foundation\Http\FormRequest;

final class ReleaseAllocationRequest extends FormRequest
{
    use ProhibitsMutationIdentitySpoofingFields;
    use ValidatesAllocationRouteId;

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
            $this->allocationRouteIdRules(),
            [
                'reason' => ['required', 'string', 'min:1'],
            ],
            $this->identitySpoofingProhibitionRules(),
        );
    }
}
