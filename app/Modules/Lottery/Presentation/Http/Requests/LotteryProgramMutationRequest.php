<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Presentation\Http\Requests;

use App\Modules\Lottery\Presentation\Http\Requests\Concerns\ProhibitsMutationIdentitySpoofingFields;
use App\Modules\Lottery\Presentation\Http\Requests\Concerns\ValidatesLotteryProgramRouteId;
use Illuminate\Foundation\Http\FormRequest;

final class LotteryProgramMutationRequest extends FormRequest
{
    use ProhibitsMutationIdentitySpoofingFields;
    use ValidatesLotteryProgramRouteId;

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
            $this->lotteryProgramRouteIdRules(),
            $this->identitySpoofingProhibitionRules(),
        );
    }
}
