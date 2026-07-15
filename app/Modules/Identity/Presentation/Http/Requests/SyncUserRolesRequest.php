<?php

declare(strict_types=1);

namespace App\Modules\Identity\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SyncUserRolesRequest extends FormRequest
{
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
            'roles' => ['required', 'array'],
            'roles.*' => [
                'integer',
                Rule::exists('roles', 'id')->where(fn ($query) => $query->where('guard_name', 'web')),
            ],
        ];
    }

    /**
     * @return list<int>
     */
    public function roleIds(): array
    {
        /** @var list<int|string> $roles */
        $roles = $this->validated('roles');

        return array_values(array_map(static fn (int|string $id): int => (int) $id, $roles));
    }
}
