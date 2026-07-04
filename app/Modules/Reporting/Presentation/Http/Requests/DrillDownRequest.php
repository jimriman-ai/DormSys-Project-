<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Presentation\Http\Requests;

use App\Modules\Reporting\Application\DTOs\AggregateDrillDownQuery;
use App\Modules\Reporting\Presentation\Http\Requests\Concerns\MapsReportingQueryParameters;
use Illuminate\Foundation\Http\FormRequest;

final class DrillDownRequest extends FormRequest
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
            'entityType' => ['required', 'string'],
            'entityId' => ['required', 'uuid'],
            'eventTypes' => ['sometimes', 'array'],
            'eventTypes.*' => ['string'],
            'occurredFrom' => ['sometimes', 'date'],
            'occurredTo' => ['sometimes', 'date'],
            'includeArchived' => ['sometimes', 'boolean'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'perPage' => ['sometimes', 'integer', 'min:1', 'max:200'],
            'drillDownHandle' => ['sometimes', 'string'],
        ];
    }

    public function toQuery(): AggregateDrillDownQuery
    {
        return new AggregateDrillDownQuery(
            entityType: (string) $this->query('entityType'),
            entityId: (string) $this->query('entityId'),
            eventTypes: $this->queryStringList('eventTypes'),
            occurredFrom: $this->queryDateTime('occurredFrom'),
            occurredTo: $this->queryDateTime('occurredTo'),
            includeArchived: $this->queryBoolean('includeArchived'),
            page: $this->queryInt('page', 1),
            perPage: $this->queryInt('perPage', 50),
            drillDownHandle: $this->query('drillDownHandle'),
        );
    }
}
