<?php

declare(strict_types=1);

namespace App\Modules\Audit\Application\Services;

use App\Modules\Audit\Application\DTOs\AuditEntryDto;

final class PayloadHashCalculator
{
    private const int MAX_SNAPSHOT_BYTES = 65536;

    /**
     * @return array{oldValues: array<string, mixed>|null, newValues: array<string, mixed>|null, metadata: array<string, mixed>|null}
     */
    public function guardSnapshots(AuditEntryDto $entry): array
    {
        $metadata = $entry->metadata ?? [];
        $oldValues = $entry->oldValues;
        $newValues = $entry->newValues;

        $oldEncoded = $oldValues === null ? '' : (string) json_encode($oldValues, JSON_THROW_ON_ERROR);
        $newEncoded = $newValues === null ? '' : (string) json_encode($newValues, JSON_THROW_ON_ERROR);

        if (strlen($oldEncoded) > self::MAX_SNAPSHOT_BYTES || strlen($newEncoded) > self::MAX_SNAPSHOT_BYTES) {
            $metadata['snapshot_truncated'] = true;
            $metadata['full_payload_hash'] = hash('sha256', $oldEncoded.$newEncoded);
            $oldValues = $this->truncateSnapshot($oldValues);
            $newValues = $this->truncateSnapshot($newValues);
        }

        return [
            'oldValues' => $oldValues,
            'newValues' => $newValues,
            'metadata' => $metadata === [] ? null : $metadata,
        ];
    }

    public function compute(AuditEntryDto $entry): string
    {
        $payload = [
            'actorId' => $entry->actorReference->actorId,
            'actorType' => $entry->actorReference->actorType->value,
            'correlationId' => $entry->correlationId->value,
            'entityId' => $entry->entityReference->entityId,
            'entityType' => $entry->entityReference->entityType,
            'eventType' => $entry->eventType->value,
            'metadata' => $entry->metadata,
            'newValues' => $entry->newValues,
            'oldValues' => $entry->oldValues,
            'sourceContext' => $entry->sourceContext,
        ];

        $this->ksortRecursive($payload);

        return hash('sha256', (string) json_encode($payload, JSON_THROW_ON_ERROR));
    }

    /**
     * @param  array<string, mixed>|null  $snapshot
     * @return array<string, mixed>|null
     */
    private function truncateSnapshot(?array $snapshot): ?array
    {
        if ($snapshot === null) {
            return null;
        }

        return ['_truncated' => true];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function ksortRecursive(array &$data): void
    {
        ksort($data);

        foreach ($data as &$value) {
            if (is_array($value)) {
                $this->ksortRecursive($value);
            }
        }
    }
}
