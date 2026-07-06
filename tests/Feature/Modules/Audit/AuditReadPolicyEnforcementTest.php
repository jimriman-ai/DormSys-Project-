<?php

declare(strict_types=1);

use App\Modules\Audit\Application\Contracts\AuditHistoryReadContract;
use App\Modules\Audit\Application\Contracts\AuditRecordingContract;
use App\Modules\Audit\Application\DTOs\AuditEntryDto;
use App\Modules\Audit\Application\DTOs\AuditHistoryQuery;
use App\Modules\Audit\Domain\Enums\ActorType;
use App\Modules\Audit\Domain\Enums\AuditEventType;
use App\Modules\Audit\Domain\Exceptions\UnauthorizedAuditAccessException;
use App\Modules\Reporting\Application\Contracts\Ports\ProjectionRefreshInputPort;
use App\Modules\Reporting\Application\Contracts\ReportingReadContract;
use App\Modules\Reporting\Application\DTOs\EntityTimelineQuery;
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
    request()->attributes->remove('audit_principal_user_id');
});

it('denies audit history read when principal is missing', function (): void {
    app(AuditRecordingContract::class)->record(AuditEntryDto::fromArray([
        'correlationId' => 'pep:audit:missing-principal',
        'eventType' => AuditEventType::RequestApproved->value,
        'entityType' => 'request',
        'entityId' => UuidGenerator::uuid7(),
        'actorType' => ActorType::User->value,
        'actorId' => UuidGenerator::uuid7(),
        'sourceContext' => 'request',
        'occurredAt' => '2026-07-02T10:00:00Z',
    ]));

    expect(fn () => app(AuditHistoryReadContract::class)->query(new AuditHistoryQuery(
        entityType: 'request',
        entityId: UuidGenerator::uuid7(),
    )))->toThrow(UnauthorizedAuditAccessException::class);
});

it('denies reporting read when principal is missing', function (): void {
    expect(fn () => app(ReportingReadContract::class)->entityTimeline(new EntityTimelineQuery(
        entityType: 'request',
        entityId: UuidGenerator::uuid7(),
    )))->toThrow(UnauthorizedAuditAccessException::class);
});

it('denies projection refresh ingest when principal is missing', function (): void {
    expect(fn () => app(ProjectionRefreshInputPort::class)->fetchNextBatch(new ProjectionRefreshRequestDto(
        projectionFamily: ProjectionFamily::Correlation,
        archiveVisibilityTier: ArchiveVisibilityTier::ActiveOnly,
        projectionVersion: '1.0.0',
    )))->toThrow(UnauthorizedAuditAccessException::class);
});

it('allows audit history read through shared policy enforcement for authorized principals', function (): void {
    $user = createIdentityUserThroughMutation('PEP Reader', 'pep-reader@example.com');
    assignRoleThroughMutation($user->requireId(), IdentityRoleSeeder::ROLE_ADMINISTRATOR);
    request()->attributes->set('audit_principal_user_id', $user->requireId()->value);

    $entityId = UuidGenerator::uuid7();
    app(AuditRecordingContract::class)->record(AuditEntryDto::fromArray([
        'correlationId' => 'pep:audit:allowed',
        'eventType' => AuditEventType::RequestApproved->value,
        'entityType' => 'request',
        'entityId' => $entityId,
        'actorType' => ActorType::User->value,
        'actorId' => UuidGenerator::uuid7(),
        'sourceContext' => 'request',
        'occurredAt' => '2026-07-02T10:00:00Z',
    ]));

    $result = app(AuditHistoryReadContract::class)->query(new AuditHistoryQuery(
        entityType: 'request',
        entityId: $entityId,
    ));

    expect($result->total)->toBe(1);
});
