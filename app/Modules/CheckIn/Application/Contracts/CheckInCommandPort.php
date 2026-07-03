<?php

declare(strict_types=1);

namespace App\Modules\CheckIn\Application\Contracts;

interface CheckInCommandPort
{
    public function checkIn(string $allocationId, string $operatorId): void;

    public function checkOut(string $allocationId, string $operatorId): void;
}
