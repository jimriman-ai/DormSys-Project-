<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Presentation\Http\Requests;

use App\Modules\Reporting\Application\DTOs\CorrelationBundleQuery;
use App\Modules\Reporting\Presentation\Http\Requests\Concerns\MapsReportingQueryParameters;
use Illuminate\Foundation\Http\FormRequest;

final class CorrelationBundleRequest extends FormRequest
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
            'correlationId' => ['required', 'string'],
            'eventTypes' => ['sometimes', 'array'],
            'eventTypes.*' => ['string'],
            'includeArchived' => ['sometimes', 'boolean'],
        ];
    }

    public function toQuery(): CorrelationBundleQuery
    {
        return new CorrelationBundleQuery(
            correlationId: (string) $this->query('correlationId'),
            includeArchived: $this->queryBoolean('includeArchived'),
            eventTypes: $this->queryStringList('eventTypes'),
        );
    }
}
