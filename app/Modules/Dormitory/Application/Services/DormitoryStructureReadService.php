<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\Services;

use App\Modules\Dormitory\Application\Contracts\DormitoryStructureReadContract;
use App\Modules\Dormitory\Application\Contracts\DormitoryStructureReadRepositoryContract;
use App\Modules\Dormitory\Application\DTOs\BedSummaryData;
use App\Modules\Dormitory\Application\DTOs\BuildingSummaryData;
use App\Modules\Dormitory\Application\DTOs\DormitoryDetailData;
use App\Modules\Dormitory\Application\DTOs\DormitorySummaryData;
use App\Modules\Dormitory\Application\DTOs\FloorSummaryData;
use App\Modules\Dormitory\Application\DTOs\RoomSummaryData;

/**
 * Read-only application service for dormitory structure queries.
 */
final class DormitoryStructureReadService implements DormitoryStructureReadContract
{
    public function __construct(
        private readonly DormitoryStructureReadRepositoryContract $reads,
    ) {}

    /**
     * @return list<DormitorySummaryData>
     */
    public function listDormitories(): array
    {
        return $this->reads->listDormitories();
    }

    public function getDormitoryDetail(string $dormitoryId): ?DormitoryDetailData
    {
        return $this->reads->findDormitoryDetail($dormitoryId);
    }

    /**
     * @return list<BuildingSummaryData>
     */
    public function listDormitoryBuildings(string $dormitoryId): array
    {
        if (! $this->reads->dormitoryExists($dormitoryId)) {
            return [];
        }

        return $this->reads->listBuildingsByDormitoryId($dormitoryId);
    }

    /**
     * @return list<FloorSummaryData>
     */
    public function listBuildingFloors(string $buildingId): array
    {
        if (! $this->reads->buildingExists($buildingId)) {
            return [];
        }

        return $this->reads->listFloorsByBuildingId($buildingId);
    }

    /**
     * @return list<RoomSummaryData>
     */
    public function listFloorRooms(string $floorId): array
    {
        if (! $this->reads->floorExists($floorId)) {
            return [];
        }

        return $this->reads->listRoomsByFloorId($floorId);
    }

    /**
     * @return list<BedSummaryData>
     */
    public function listRoomBeds(string $roomId): array
    {
        if (! $this->reads->roomExists($roomId)) {
            return [];
        }

        return $this->reads->listBedsByRoomId($roomId);
    }
}
