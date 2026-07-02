<?php

declare(strict_types=1);

namespace App\Modules\Audit\Application\Contracts;

use App\Modules\Audit\Application\DTOs\AuditEntryDto;
use App\Modules\Audit\Application\DTOs\AuditHistoryQuery;
use App\Modules\Audit\Application\DTOs\AuditRecordResultDto;
use App\Modules\Audit\Application\DTOs\PaginatedAuditHistoryDto;
use App\Modules\Audit\Domain\Models\AuditLog;

interface AuditRecordingContract
{
    public function record(AuditEntryDto $entry): AuditRecordResultDto;
}

interface AuditHistoryReadContract
{
    public function query(AuditHistoryQuery $query): PaginatedAuditHistoryDto;
}

interface AuditAuthorizationPort
{
    public function authorizeRead(): void;
}

interface AuditLogRepositoryContract
{
    public function insert(AuditLog $auditLog): AuditLog;

    public function findByCorrelationId(string $correlationId): ?AuditLog;

    public function queryHistory(AuditHistoryQuery $query): PaginatedAuditHistoryDto;

    public function archiveExpiredBefore(\DateTimeImmutable $cutoff): int;
}
