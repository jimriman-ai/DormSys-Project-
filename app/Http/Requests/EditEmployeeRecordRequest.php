<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Auth\EmployeeRecordsPolicyEnforcementPoint;
use App\Modules\Employee\Domain\Enums\EmployeeStatus;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class EditEmployeeRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user('api');

        if (! $user instanceof UserModel) {
            return false;
        }

        return app(EmployeeRecordsPolicyEnforcementPoint::class)->canEdit($user);
    }

    /**
     * Field contract aligned to employee_employees schema and EmployeeHub create rules.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'employee_code' => ['sometimes', 'required', 'string', 'max:100'],
            'first_name' => ['sometimes', 'required', 'string', 'max:100'],
            'last_name' => ['sometimes', 'required', 'string', 'max:100'],
            'national_code' => ['sometimes', 'required', 'string', 'size:10', 'regex:/^\d{10}$/'],
            'department_id' => ['sometimes', 'nullable', 'uuid'],
            'hire_date' => ['sometimes', 'required', 'date_format:Y-m-d'],
            'base_lottery_score' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', 'required', 'string', Rule::in([
                EmployeeStatus::Active->value,
                EmployeeStatus::Inactive->value,
            ])],
        ];
    }
}
