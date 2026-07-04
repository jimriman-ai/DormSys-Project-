<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Presentation\Http\Requests;

use App\Modules\Reporting\Application\DTOs\AuditWindowSummaryQuery;
use App\Modules\Reporting\Presentation\Http\Requests\Concerns\MapsReportingQueryParameters;
use Illuminate\Foundation\Http\FormRequest;

final class AuditWindowSummaryRequest extends FormRequest
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
            'windowStart' => ['required', 'date'],
            'windowEnd' => ['required', 'date'],
            'granularity' => ['sometimes', 'string', 'in:hour,day,week,month'],
            'eventType' => ['sometimes', 'string'],
            'sourceContext' => ['sometimes', 'string'],
            'actorType' => ['sometimes', 'string'],
            'entityType' => ['sometimes', 'string'],
            'includeArchived' => ['sometimes', 'boolean'],
        ];
    }

    public function toQuery(): AuditWindowSummaryQuery
    {
        return new AuditWindowSummaryQuery(
            windowStart: $this->requiredDateTime('windowStart'),
            windowEnd: $this->requiredDateTime('windowEnd'),
            granularity: $this->queryGranularity(),
            eventType: $this->query('eventType'),
            sourceContext: $this->query('sourceContext'),
            actorType: $this->query('actorType'),
            entityType: $this->query('entityType'),
            includeArchived: $this->queryBoolean('includeArchived'),
        );
    }
}
