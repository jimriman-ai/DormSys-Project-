<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Presentation\Http\Requests;

use App\Modules\Reporting\Application\DTOs\SecurityActorActivityQuery;
use App\Modules\Reporting\Presentation\Http\Requests\Concerns\MapsReportingQueryParameters;
use Illuminate\Foundation\Http\FormRequest;

final class SecurityActorActivityRequest extends FormRequest
{
    use MapsReportingQueryParameters;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'actorType' => ['required', 'string'],
            'actorId' => ['required', 'uuid'],
            'windowStart' => ['required', 'date'],
            'windowEnd' => ['required', 'date'],
            'granularity' => ['sometimes', 'string', 'in:hour,day,week,month'],
            'includeArchived' => ['sometimes', 'boolean'],
        ];
    }

    public function toQuery(): SecurityActorActivityQuery
    {
        return new SecurityActorActivityQuery(
            actorType: (string) $this->query('actorType'),
            actorId: (string) $this->query('actorId'),
            windowStart: $this->requiredDateTime('windowStart'),
            windowEnd: $this->requiredDateTime('windowEnd'),
            granularity: $this->queryGranularity(),
            includeArchived: $this->queryBoolean('includeArchived'),
        );
    }
}
