<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Presentation\Http\Requests;

use App\Modules\Lottery\Presentation\Http\Requests\Concerns\ProhibitsMutationIdentitySpoofingFields;
use Illuminate\Foundation\Http\FormRequest;

final class CreateLotteryProgramRequest extends FormRequest
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
                'title' => ['required', 'string', 'max:255'],
                'dormitoryId' => ['required', 'uuid'],
                'capacity' => ['required', 'integer', 'min:1'],
                'registrationStartsAt' => ['required', 'date'],
                'registrationEndsAt' => ['required', 'date', 'after:registrationStartsAt'],
            ],
            $this->identitySpoofingProhibitionRules(),
        );
    }
}
