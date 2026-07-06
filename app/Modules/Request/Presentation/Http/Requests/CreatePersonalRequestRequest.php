<?php

declare(strict_types=1);

namespace App\Modules\Request\Presentation\Http\Requests;

use App\Modules\Request\Presentation\Http\Requests\Concerns\ProhibitsMutationIdentitySpoofingFields;
use Illuminate\Foundation\Http\FormRequest;

final class CreatePersonalRequestRequest extends FormRequest
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
                'dormitoryId' => ['required', 'uuid'],
                'checkInDate' => ['required', 'date_format:Y-m-d'],
                'checkOutDate' => ['required', 'date_format:Y-m-d', 'after:checkInDate'],
            ],
            $this->identitySpoofingProhibitionRules(),
        );
    }
}
