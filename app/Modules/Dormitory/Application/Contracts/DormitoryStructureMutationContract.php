<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\Contracts;

use App\Modules\Dormitory\Application\DTOs\BedOccupancyChangedData;
use App\Modules\Dormitory\Application\DTOs\CreateBedData;
use App\Modules\Dormitory\Application\DTOs\CreateBuildingData;
use App\Modules\Dormitory\Application\DTOs\CreateDormitoryData;
use App\Modules\Dormitory\Application\DTOs\CreatedResourceData;
use App\Modules\Dormitory\Application\DTOs\CreateFloorData;
use App\Modules\Dormitory\Application\DTOs\CreateRoomData;
use App\Modules\Dormitory\Application\DTOs\ResourceStatusChangedData;

/**
 * Phase 3C application mutation surface for dormitory structure and physical state.
 */
interface DormitoryStructureMutationContract
{
    public function createDormitory(CreateDormitoryData $data): CreatedResourceData;

    public function createBuilding(CreateBuildingData $data): CreatedResourceData;

    public function createFloor(CreateFloorData $data): CreatedResourceData;

    public function createRoom(CreateRoomData $data): CreatedResourceData;

    public function createBed(CreateBedData $data): CreatedResourceData;

    public function changeDormitoryStatus(string $dormitoryId, string $status): ResourceStatusChangedData;

    public function changeRoomStatus(string $roomId, string $status): ResourceStatusChangedData;

    public function changeBedStatus(string $bedId, string $status): ResourceStatusChangedData;

    public function recordBedOccupancyStart(string $bedId): BedOccupancyChangedData;

    public function recordBedOccupancyEnd(string $bedId): BedOccupancyChangedData;
}
