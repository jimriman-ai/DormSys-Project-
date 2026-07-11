<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\DTOs;

/**
 * Input for CreateRoom.
 */
final readonly class CreateRoomData
{
    public function __construct(
        public string $floorId,
        public string $code,
        public string $name,
        public int $capacityTotal,
        public ?string $status = null,
    ) {}
}
