<?php

declare(strict_types=1);

namespace App\Modules\Request\Presentation\Http\Requests;

use App\Modules\Request\Presentation\Http\Requests\Concerns\ProhibitsMutationIdentitySpoofingFields;
use App\Modules\Request\Presentation\Http\Requests\Concerns\ValidatesRequestRouteId;
use Illuminate\Foundation\Http\FormRequest;

final class ApproveRequestStageRequest extends FormRequest
{
    use ProhibitsMutationIdentitySpoofingFields;
    use ValidatesRequestRouteId;

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
            $this->identitySpoofingProhibitionRules(),
        );
    }
}
