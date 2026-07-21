<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\Contracts;

use App\Modules\Dormitory\Application\DTOs\BedSummaryData;
use App\Modules\Dormitory\Application\DTOs\BuildingSummaryData;
use App\Modules\Dormitory\Application\DTOs\DormitoryDetailData;
use App\Modules\Dormitory\Application\DTOs\DormitorySummaryData;
use App\Modules\Dormitory\Application\DTOs\FloorSummaryData;
use App\Modules\Dormitory\Application\DTOs\RoomSummaryData;

/**
 * Persistence-backed read access for dormitory structure queries.
 */
interface DormitoryStructureReadRepositoryContract
{
    /**
     * @return list<DormitorySummaryData>
     */
    public function listDormitories(): array;

    public function findDormitoryDetail(string $dormitoryId): ?DormitoryDetailData;

    /**
     * Active-assignment-scoped dormitory summaries for an identity user (employee UI).
     *
     * @return list<DormitorySummaryData>
     */
    public function listAssignedDormitoriesForUser(string $identityUserId): array;

    /**
     * Dormitory detail when the identity user has an active assignment; otherwise null.
     */
    public function findAssignedDormitoryDetailForUser(string $identityUserId, string $dormitoryId): ?DormitoryDetailData;

    /**
     * @return list<BuildingSummaryData>
     */
    public function listBuildingsByDormitoryId(string $dormitoryId): array;

    /**
     * @return list<FloorSummaryData>
     */
    public function listFloorsByBuildingId(string $buildingId): array;

    /**
     * @return list<RoomSummaryData>
     */
    public function listRoomsByFloorId(string $floorId): array;

    /**
     * @return list<BedSummaryData>
     */
    public function listBedsByRoomId(string $roomId): array;

    public function dormitoryExists(string $dormitoryId): bool;

    public function buildingExists(string $buildingId): bool;

    public function floorExists(string $floorId): bool;

    public function roomExists(string $roomId): bool;
}
