<?php

declare(strict_types=1);

use App\Modules\Audit\Application\Contracts\AuditRecordingContract;
use App\Modules\Audit\Application\DTOs\AuditEntryDto;
use App\Modules\Audit\Domain\Enums\ActorType;
use App\Modules\Audit\Domain\Enums\AuditEventType;
use App\Modules\Audit\Domain\Exceptions\UnauthorizedAuditAccessException;
use App\Modules\Identity\Domain\Enums\UserStatus;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Modules\Reporting\Application\Contracts\Ports\ProjectionCursorControlPort;
use App\Modules\Reporting\Application\Contracts\Ports\ProjectionRefreshInputPort;
use App\Modules\Reporting\Application\DTOs\ProjectionRefreshRequestDto;
use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use App\Modules\Reporting\Domain\Enums\ProjectionFamily;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['audit.sync_in_tests' => true]);
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);

    $user = UserModel::query()->create([
        'display_name' => 'Refresh Reader',
        'email' => 'refresh-reader@example.com',
        'status' => UserStatus::Active,
    ]);
    $user->assignRole(IdentityRoleSeeder::ROLE_ADMINISTRATOR);
    request()->attributes->set('audit_principal_user_id', $user->id);
});

function recordRefreshableAuditEntry(
    ?DateTimeImmutable $occurredAt = null,
): void {
    app(AuditRecordingContract::class)->record(AuditEntryDto::fromArray([
        'correlationId' => 'reporting:refresh:'.UuidGenerator::uuid7(),
        'eventType' => AuditEventType::RequestSubmitted->value,
        'entityType' => 'request',
        'entityId' => UuidGenerator::uuid7(),
        'actorType' => ActorType::User->value,
        'actorId' => UuidGenerator::uuid7(),
        'sourceContext' => 'request',
        'occurredAt' => ($occurredAt ?? new DateTimeImmutable('2026-07-01T10:00:00+00:00'))->format(DateTimeInterface::ATOM),
    ]));
}

it('fetches a T0 refresh batch without advancing the cursor', function (): void {
    recordRefreshableAuditEntry();

    $refresh = app(ProjectionRefreshInputPort::class);

    $batch = $refresh->fetchNextBatch(new ProjectionRefreshRequestDto(
        projectionFamily: ProjectionFamily::Correlation,
        archiveVisibilityTier: ArchiveVisibilityTier::ActiveOnly,
        projectionVersion: '1.0.0',
    ));

    expect($batch->items)->not->toBeEmpty();
    expect($batch->cursor->lastSourceAuditLogId)->toBeNull();
    expect($batch->hasMorePages)->toBeFalse();
});

it('resumes refresh ingest after cursor advancement', function (): void {
    recordRefreshableAuditEntry(occurredAt: new DateTimeImmutable('2026-07-01T09:00:00+00:00'));
    recordRefreshableAuditEntry(occurredAt: new DateTimeImmutable('2026-07-01T11:00:00+00:00'));

    $refresh = app(ProjectionRefreshInputPort::class);
    $control = app(ProjectionCursorControlPort::class);

    $firstBatch = $refresh->fetchNextBatch(new ProjectionRefreshRequestDto(
        projectionFamily: ProjectionFamily::Correlation,
        archiveVisibilityTier: ArchiveVisibilityTier::ActiveOnly,
        projectionVersion: '1.0.0',
        perPage: 1,
    ));

    expect($firstBatch->items)->toHaveCount(1);

    $latestItem = $firstBatch->items[0];

    $control->advanceAfterSuccessfulBatch(
        $firstBatch->cursor->id,
        $latestItem->auditLogId,
        $latestItem->occurredAt,
        '1.0.0',
    );

    $secondBatch = $refresh->fetchNextBatch(new ProjectionRefreshRequestDto(
        projectionFamily: ProjectionFamily::Correlation,
        archiveVisibilityTier: ArchiveVisibilityTier::ActiveOnly,
        projectionVersion: '1.0.0',
        perPage: 10,
    ));

    expect($secondBatch->items)->toHaveCount(1);
    expect($secondBatch->items[0]->auditLogId)->not->toBe($latestItem->auditLogId);
});

it('denies refresh ingest without audit.read permission', function (): void {
    recordRefreshableAuditEntry();

    $user = createIdentityUserThroughMutation('No Refresh Access', 'no-refresh@example.com');
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    request()->attributes->set('audit_principal_user_id', $model->id);

    expect(fn () => app(ProjectionRefreshInputPort::class)->fetchNextBatch(new ProjectionRefreshRequestDto(
        projectionFamily: ProjectionFamily::Correlation,
        archiveVisibilityTier: ArchiveVisibilityTier::ActiveOnly,
        projectionVersion: '1.0.0',
    )))->toThrow(UnauthorizedAuditAccessException::class);
});

it('denies include archived refresh tier without audit.read permission', function (): void {
    recordRefreshableAuditEntry();

    $user = createIdentityUserThroughMutation('No Archived Refresh', 'no-archived-refresh@example.com');
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    request()->attributes->set('audit_principal_user_id', $model->id);

    expect(fn () => app(ProjectionRefreshInputPort::class)->fetchNextBatch(new ProjectionRefreshRequestDto(
        projectionFamily: ProjectionFamily::Correlation,
        archiveVisibilityTier: ArchiveVisibilityTier::IncludeArchived,
        projectionVersion: '1.0.0',
    )))->toThrow(UnauthorizedAuditAccessException::class);
});
