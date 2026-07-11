<?php

declare(strict_types=1);

namespace App\Modules\Dormitory\Application\Contracts;

use App\Modules\Dormitory\Domain\Enums\PhysicalOccupancyState;
use App\Modules\Dormitory\Domain\Enums\ResourceStatus;

/**
 * Persistence write access for Phase 3C hierarchy and physical-state mutations.
 */
interface DormitoryStructureWriteRepositoryContract
{
    public function createDormitory(string $id, string $code, string $name, ResourceStatus $status): void;

    public function createBuilding(
        string $id,
        string $dormitoryId,
        string $code,
        string $name,
        ResourceStatus $status,
    ): void;

    public function createFloor(
        string $id,
        string $buildingId,
        string $label,
        ResourceStatus $status,
    ): void;

    public function createRoom(
        string $id,
        string $floorId,
        string $code,
        string $name,
        int $capacityTotal,
        ResourceStatus $status,
    ): void;

    public function createBed(
        string $id,
        string $roomId,
        string $label,
        ResourceStatus $status,
        PhysicalOccupancyState $occupancy,
    ): void;

    public function updateDormitoryStatus(string $id, ResourceStatus $status): void;

    public function updateRoomStatus(string $id, ResourceStatus $status): void;

    public function updateBedStatus(string $id, ResourceStatus $status): void;

    public function updateBedOccupancy(string $id, PhysicalOccupancyState $occupancy): void;

    public function dormitoryExists(string $id): bool;

    public function buildingExists(string $id): bool;

    public function floorExists(string $id): bool;

    public function roomExists(string $id): bool;

    public function bedExists(string $id): bool;

    /**
     * @return array{
     *     id: string,
     *     code: string,
     *     name: string,
     *     status: ResourceStatus
     * }|null
     */
    public function findDormitory(string $id): ?array;

    /**
     * @return array{
     *     id: string,
     *     floor_id: string,
     *     code: string,
     *     name: string,
     *     capacity_total: int,
     *     status: ResourceStatus
     * }|null
     */
    public function findRoom(string $id): ?array;

    /**
     * @return array{
     *     id: string,
     *     room_id: string,
     *     label: string,
     *     status: ResourceStatus,
     *     occupancy: PhysicalOccupancyState
     * }|null
     */
    public function findBed(string $id): ?array;

    /**
     * @return list<array{
     *     id: string,
     *     label: string,
     *     status: ResourceStatus,
     *     occupancy: PhysicalOccupancyState
     * }>
     */
    public function listBedsByRoomId(string $roomId): array;
}
