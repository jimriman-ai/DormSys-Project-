<?php

declare(strict_types=1);

use App\Modules\Audit\Application\Contracts\AuditRecordingContract;
use App\Modules\Audit\Application\DTOs\AuditEntryDto;
use App\Modules\Audit\Domain\Enums\ActorType;
use App\Modules\Audit\Domain\Enums\AuditEventType;
use App\Modules\Identity\Application\Services\AssignRoleToUserAction;
use App\Modules\Identity\Application\Services\CreateUserAction;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Modules\Reporting\Application\Contracts\Ports\ProjectionRefreshRunnerPort;
use App\Modules\Reporting\Application\Contracts\ReportingReadContract;
use App\Modules\Reporting\Application\DTOs\AuditWindowSummaryQuery;
use App\Modules\Reporting\Application\DTOs\CorrelationBundleQuery;
use App\Modules\Reporting\Application\DTOs\ProjectionRefreshRequestDto;
use App\Modules\Reporting\Application\DTOs\SecurityActorActivityQuery;
use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use App\Modules\Reporting\Domain\Enums\ProjectionFamily;
use App\Modules\Reporting\Domain\Enums\WindowGranularity;
use App\Modules\Reporting\Infrastructure\Persistence\Models\CorrelationProjectionEntryModel;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['audit.sync_in_tests' => true]);
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);

    $user = app(CreateUserAction::class)->execute('Projection Reader', 'projection-reader@example.com');
    app(AssignRoleToUserAction::class)->execute($user->requireId(), IdentityRoleSeeder::ROLE_ADMINISTRATOR);
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    request()->attributes->set('audit_principal_user_id', $model->id);
});

function seedProjectionBackedAuditEntry(array $overrides = []): array
{
    $correlationId = $overrides['correlationId'] ?? 'reporting:projection:'.UuidGenerator::uuid7();
    $actorId = $overrides['actorId'] ?? UuidGenerator::uuid7();

    app(AuditRecordingContract::class)->record(AuditEntryDto::fromArray(array_merge([
        'correlationId' => $correlationId,
        'eventType' => AuditEventType::RequestSubmitted->value,
        'entityType' => 'request',
        'entityId' => UuidGenerator::uuid7(),
        'actorType' => ActorType::User->value,
        'actorId' => $actorId,
        'sourceContext' => 'request',
        'occurredAt' => '2026-07-01T10:00:00+00:00',
    ], $overrides)));

    return [
        'correlationId' => $correlationId,
        'actorType' => $overrides['actorType'] ?? ActorType::User->value,
        'actorId' => $actorId,
    ];
}

function materializeProjectionFamily(ProjectionFamily $family): void
{
    app(ProjectionRefreshRunnerPort::class)->runBatch(new ProjectionRefreshRequestDto(
        projectionFamily: $family,
        archiveVisibilityTier: ArchiveVisibilityTier::ActiveOnly,
        projectionVersion: '1.0.0',
    ));
}

it('reads RU-02 correlation bundle from persisted projection rows', function (): void {
    $seed = seedProjectionBackedAuditEntry([
        'correlationId' => 'reporting:ru02:bundle',
    ]);

    materializeProjectionFamily(ProjectionFamily::Correlation);

    $result = app(ReportingReadContract::class)->correlationBundle(new CorrelationBundleQuery(
        correlationId: $seed['correlationId'],
    ));

    expect($result->correlationId)->toBe('reporting:ru02:bundle');
    expect($result->itemCount)->toBeGreaterThan(0);
    expect($result->items)->not->toBeEmpty();
    expect($result->provenance->sourceTier)->toBe('T1');
    expect($result->provenance->projectionVersion)->not->toBeNull();

    $auditLogIds = array_map(static fn ($item) => $item->auditLogId, $result->items);
    expect($auditLogIds)->toBe(array_values(array_unique($auditLogIds)));
});

it('returns stable empty RU-02 correlation bundle when no projection rows exist', function (): void {
    $result = app(ReportingReadContract::class)->correlationBundle(new CorrelationBundleQuery(
        correlationId: 'reporting:ru02:missing',
    ));

    expect($result->itemCount)->toBe(0);
    expect($result->items)->toBe([]);
    expect($result->occurredAtMin)->toBeNull();
    expect($result->occurredAtMax)->toBeNull();
    expect($result->provenance->sourceTier)->toBe('T1');
});

it('does not duplicate RU-02 correlation items after idempotent materialization replay', function (): void {
    $seed = seedProjectionBackedAuditEntry([
        'correlationId' => 'reporting:ru02:idempotent',
    ]);

    materializeProjectionFamily(ProjectionFamily::Correlation);
    materializeProjectionFamily(ProjectionFamily::Correlation);

    $projectionCount = CorrelationProjectionEntryModel::query()
        ->where('correlation_id', $seed['correlationId'])
        ->count();

    $result = app(ReportingReadContract::class)->correlationBundle(new CorrelationBundleQuery(
        correlationId: $seed['correlationId'],
    ));

    expect($result->itemCount)->toBe($projectionCount);
    expect($result->itemCount)->toBeGreaterThan(0);
});

it('reads RU-03 audit window summary from persisted aggregate rows', function (): void {
    seedProjectionBackedAuditEntry([
        'eventType' => AuditEventType::RequestSubmitted->value,
        'sourceContext' => 'request',
    ]);

    materializeProjectionFamily(ProjectionFamily::WindowAggregate);

    $windowStart = new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC'));
    $windowEnd = new DateTimeImmutable('2026-07-02 00:00:00', new DateTimeZone('UTC'));

    $result = app(ReportingReadContract::class)->auditWindowSummary(new AuditWindowSummaryQuery(
        windowStart: $windowStart,
        windowEnd: $windowEnd,
        granularity: WindowGranularity::Day,
        eventType: AuditEventType::RequestSubmitted->value,
        sourceContext: 'request',
        actorType: ActorType::User->value,
        entityType: 'request',
    ));

    expect($result->buckets)->not->toBeEmpty();
    expect($result->buckets[0]->eventCount)->toBeGreaterThan(0);
    expect($result->provenance->sourceTier)->toBe('T1');
    expect($result->provenance->projectionVersion)->toBe('1.0.0');
});

it('returns stable empty RU-03 audit window summary when no aggregates match', function (): void {
    $windowStart = new DateTimeImmutable('2099-01-01 00:00:00', new DateTimeZone('UTC'));
    $windowEnd = new DateTimeImmutable('2099-01-02 00:00:00', new DateTimeZone('UTC'));

    $result = app(ReportingReadContract::class)->auditWindowSummary(new AuditWindowSummaryQuery(
        windowStart: $windowStart,
        windowEnd: $windowEnd,
    ));

    expect($result->buckets)->toBe([]);
    expect($result->provenance->sourceTier)->toBe('T1');
    expect($result->provenance->refreshedAt)->toBeNull();
});

it('reads RU-05 security actor activity from persisted summary rows', function (): void {
    $seed = seedProjectionBackedAuditEntry([
        'actorId' => UuidGenerator::uuid7(),
    ]);

    materializeProjectionFamily(ProjectionFamily::ActorActivity);

    $windowStart = new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC'));
    $windowEnd = new DateTimeImmutable('2026-07-02 00:00:00', new DateTimeZone('UTC'));

    $result = app(ReportingReadContract::class)->securityActorActivity(new SecurityActorActivityQuery(
        actorType: $seed['actorType'],
        actorId: $seed['actorId'],
        windowStart: $windowStart,
        windowEnd: $windowEnd,
        granularity: WindowGranularity::Day,
    ));

    expect($result->summaries)->not->toBeEmpty();
    expect($result->summaries[0]->actorId)->toBe($seed['actorId']);
    expect($result->summaries[0]->eventCount)->toBeGreaterThan(0);
    expect($result->provenance->sourceTier)->toBe('T1');
});

it('returns stable empty RU-05 security actor activity when no summaries match', function (): void {
    $windowStart = new DateTimeImmutable('2099-01-01 00:00:00', new DateTimeZone('UTC'));
    $windowEnd = new DateTimeImmutable('2099-01-02 00:00:00', new DateTimeZone('UTC'));

    $result = app(ReportingReadContract::class)->securityActorActivity(new SecurityActorActivityQuery(
        actorType: ActorType::User->value,
        actorId: UuidGenerator::uuid7(),
        windowStart: $windowStart,
        windowEnd: $windowEnd,
    ));

    expect($result->summaries)->toBe([]);
    expect($result->provenance->sourceTier)->toBe('T1');
    expect($result->provenance->refreshedAt)->toBeNull();
});

it('keeps RU-02 RU-03 and RU-05 read paths independently verifiable', function (): void {
    $seed = seedProjectionBackedAuditEntry([
        'correlationId' => 'reporting:ru:isolated',
    ]);

    materializeProjectionFamily(ProjectionFamily::Correlation);

    $correlation = app(ReportingReadContract::class)->correlationBundle(new CorrelationBundleQuery(
        correlationId: $seed['correlationId'],
    ));

    $windowStart = new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC'));
    $windowEnd = new DateTimeImmutable('2026-07-02 00:00:00', new DateTimeZone('UTC'));

    $window = app(ReportingReadContract::class)->auditWindowSummary(new AuditWindowSummaryQuery(
        windowStart: $windowStart,
        windowEnd: $windowEnd,
    ));

    $actor = app(ReportingReadContract::class)->securityActorActivity(new SecurityActorActivityQuery(
        actorType: $seed['actorType'],
        actorId: $seed['actorId'],
        windowStart: $windowStart,
        windowEnd: $windowEnd,
    ));

    expect($correlation->itemCount)->toBeGreaterThan(0);
    expect($window->buckets)->toBe([]);
    expect($actor->summaries)->toBe([]);
});
