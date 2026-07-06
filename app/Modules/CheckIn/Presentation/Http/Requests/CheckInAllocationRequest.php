<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Presentation\Http\Requests;

use App\Modules\CheckIn\Presentation\Http\Requests\Concerns\ProhibitsMutationIdentitySpoofingFields;
use App\Modules\CheckIn\Presentation\Http\Requests\Concerns\ValidatesCheckInAllocationRouteId;
use Illuminate\Foundation\Http\FormRequest;

final class CheckInAllocationRequest extends FormRequest
{
    use ProhibitsMutationIdentitySpoofingFields;
    use ValidatesCheckInAllocationRouteId;

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
            $this->checkInAllocationRouteIdRules(),
            $this->identitySpoofingProhibitionRules(),
        );
    }
}
