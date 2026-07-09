<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\DTOs;

final readonly class EmployeeRequestListQueryDTO
{
    public function __construct(
        public string $employeeId,
        public ?string $status,
        public string $sortField,
        public string $sortDirection,
        public int $page,
        public int $perPage = 15,
    ) {}
}
