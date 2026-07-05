<?php

declare(strict_types=1);

use App\Modules\Audit\Application\Contracts\AuditRecordingContract;
use App\Modules\Audit\Domain\Enums\AuditEventType;
use App\Modules\Audit\Infrastructure\Persistence\Models\AuditLogModel;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\Support\Audit\AuditProducerTestDouble;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['audit.sync_in_tests' => true]);
});

it('records audit entries from an external producer test double without audit importing producer infrastructure', function (): void {
    $correlationId = 'test-double:producer:deterministic-001';
    $auditLogId = app(AuditProducerTestDouble::class)->emitSampleEntry($correlationId);

    $model = AuditLogModel::query()->findOrFail($auditLogId);
    expect($model->correlation_id)->toBe($correlationId);
    expect($model->event_type)->toBe(AuditEventType::RequestSubmitted);
    expect($model->source_context)->toBe('test_double');
    expect($model->metadata)->toMatchArray(['producer' => 'AuditProducerTestDouble']);
});

it('keeps audit module free of upstream producer infrastructure imports', function (): void {
    $auditModulePath = app_path('Modules/Audit');
    $producerInfrastructureMarker = 'Tests\\Support\\Audit\\AuditProducerTestDouble';

    foreach (File::allFiles($auditModulePath) as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        expect(file_get_contents($file->getPathname()))
            ->not->toContain($producerInfrastructureMarker);
    }

    app(AuditRecordingContract::class);
});

it('remains idempotent when the producer test double emits the same correlation identifier twice', function (): void {
    $producer = app(AuditProducerTestDouble::class);
    $correlationId = 'test-double:producer:idempotent-001';
    $entityId = UuidGenerator::uuid7();
    $occurredAt = new DateTimeImmutable('2026-07-02T12:00:00Z', new DateTimeZone('UTC'));

    $firstId = $producer->emitSampleEntry($correlationId, $occurredAt, $entityId);
    $secondId = $producer->emitSampleEntry($correlationId, $occurredAt, $entityId);

    expect($secondId)->toBe($firstId);
    expect(AuditLogModel::query()->where('correlation_id', $correlationId)->count())->toBe(1);
});
