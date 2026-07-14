<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Auth\StudentRecordsPolicyEnforcementPoint;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use Illuminate\Foundation\Http\FormRequest;

final class EditStudentRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user('api');

        if (! $user instanceof UserModel) {
            return false;
        }

        return app(StudentRecordsPolicyEnforcementPoint::class)->canEdit($user);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
