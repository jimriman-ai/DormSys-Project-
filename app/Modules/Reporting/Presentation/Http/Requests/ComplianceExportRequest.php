<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Presentation\Http\Requests;

use App\Modules\Reporting\Application\DTOs\ComplianceExportQuery;
use App\Modules\Reporting\Presentation\Http\Requests\Concerns\MapsReportingQueryParameters;
use Illuminate\Foundation\Http\FormRequest;

final class ComplianceExportRequest extends FormRequest
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
            'eventTypes' => ['sometimes', 'array'],
            'eventTypes.*' => ['string'],
            'eventType' => ['sometimes', 'string'],
            'sourceContext' => ['sometimes', 'string'],
            'actorType' => ['sometimes', 'string'],
            'entityType' => ['sometimes', 'string'],
            'includeArchived' => ['sometimes', 'boolean'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'perPage' => ['sometimes', 'integer', 'min:1', 'max:200'],
        ];
    }

    public function toQuery(): ComplianceExportQuery
    {
        return new ComplianceExportQuery(
            windowStart: $this->requiredDateTime('windowStart'),
            windowEnd: $this->requiredDateTime('windowEnd'),
            granularity: $this->queryGranularity(),
            eventTypes: $this->queryStringList('eventTypes'),
            eventType: $this->query('eventType'),
            sourceContext: $this->query('sourceContext'),
            actorType: $this->query('actorType'),
            entityType: $this->query('entityType'),
            includeArchived: $this->queryBoolean('includeArchived'),
            page: $this->queryInt('page', 1),
            perPage: $this->queryInt('perPage', 50),
        );
    }
}
