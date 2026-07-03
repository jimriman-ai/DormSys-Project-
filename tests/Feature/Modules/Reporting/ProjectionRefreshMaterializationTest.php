<?php

declare(strict_types=1);

use App\Modules\Audit\Application\Contracts\AuditRecordingContract;
use App\Modules\Audit\Application\DTOs\AuditEntryDto;
use App\Modules\Audit\Domain\Enums\ActorType;
use App\Modules\Audit\Domain\Enums\AuditEventType;
use App\Modules\Identity\Application\Services\AssignRoleToUserAction;
use App\Modules\Identity\Application\Services\CreateUserAction;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Modules\Reporting\Application\Contracts\Ports\ProjectionRefreshMaterializerPort;
use App\Modules\Reporting\Application\Contracts\Ports\ProjectionRefreshRunnerPort;
use App\Modules\Reporting\Application\DTOs\ProjectionRefreshRequestDto;
use App\Modules\Reporting\Application\Services\ProjectionRefreshMaterializerRegistry;
use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use App\Modules\Reporting\Domain\Enums\ProjectionCursorStatus;
use App\Modules\Reporting\Domain\Enums\ProjectionFamily;
use App\Modules\Reporting\Infrastructure\Persistence\Models\ActorActivitySummaryModel;
use App\Modules\Reporting\Infrastructure\Persistence\Models\AuditWindowAggregateModel;
use App\Modules\Reporting\Infrastructure\Persistence\Models\CorrelationProjectionEntryModel;
use App\Modules\Reporting\Infrastructure\Persistence\Models\ProjectionCursorModel;
use App\Modules\Reporting\Infrastructure\Repositories\ProjectionCursorRepository;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['audit.sync_in_tests' => true]);
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);

    $user = app(CreateUserAction::class)->execute('Materialize Reader', 'materialize-reader@example.com');
    app(AssignRoleToUserAction::class)->execute($user->requireId(), IdentityRoleSeeder::ROLE_ADMINISTRATOR);
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    request()->attributes->set('audit_principal_user_id', $model->id);
});

function seedMaterializationAuditEntry(array $overrides = []): void
{
    app(AuditRecordingContract::class)->record(AuditEntryDto::fromArray(array_merge([
        'correlationId' => 'reporting:materialize:'.UuidGenerator::uuid7(),
        'eventType' => AuditEventType::RequestSubmitted->value,
        'entityType' => 'request',
        'entityId' => UuidGenerator::uuid7(),
        'actorType' => ActorType::User->value,
        'actorId' => UuidGenerator::uuid7(),
        'sourceContext' => 'request',
        'occurredAt' => '2026-07-01T10:00:00+00:00',
    ], $overrides)));
}

function runMaterializationBatch(ProjectionFamily $family): void
{
    app(ProjectionRefreshRunnerPort::class)->runBatch(new ProjectionRefreshRequestDto(
        projectionFamily: $family,
        archiveVisibilityTier: ArchiveVisibilityTier::ActiveOnly,
        projectionVersion: '1.0.0',
    ));
}

it('materializes correlation projection rows from a refresh batch', function (): void {
    seedMaterializationAuditEntry();

    $result = app(ProjectionRefreshRunnerPort::class)->runBatch(new ProjectionRefreshRequestDto(
        projectionFamily: ProjectionFamily::Correlation,
        archiveVisibilityTier: ArchiveVisibilityTier::ActiveOnly,
        projectionVersion: '1.0.0',
    ));

    expect($result->itemsFetched)->toBeGreaterThan(0);
    expect($result->itemsMaterialized)->toBe($result->itemsFetched);
    expect(CorrelationProjectionEntryModel::query()->count())->toBeGreaterThan(0);
    expect($result->cursor->lastSourceAuditLogId)->not->toBeNull();
    expect($result->cursor->status)->toBe(ProjectionCursorStatus::Idle);
});

it('materializes audit window aggregate rows from a refresh batch', function (): void {
    seedMaterializationAuditEntry();

    $result = app(ProjectionRefreshRunnerPort::class)->runBatch(new ProjectionRefreshRequestDto(
        projectionFamily: ProjectionFamily::WindowAggregate,
        archiveVisibilityTier: ArchiveVisibilityTier::ActiveOnly,
        projectionVersion: '1.0.0',
    ));

    expect($result->itemsFetched)->toBeGreaterThan(0);
    expect($result->itemsMaterialized)->toBe($result->itemsFetched);
    expect(AuditWindowAggregateModel::query()->count())->toBeGreaterThan(0);
});

it('materializes actor activity summary rows from a refresh batch', function (): void {
    seedMaterializationAuditEntry();

    $result = app(ProjectionRefreshRunnerPort::class)->runBatch(new ProjectionRefreshRequestDto(
        projectionFamily: ProjectionFamily::ActorActivity,
        archiveVisibilityTier: ArchiveVisibilityTier::ActiveOnly,
        projectionVersion: '1.0.0',
    ));

    expect($result->itemsFetched)->toBeGreaterThan(0);
    expect($result->itemsMaterialized)->toBe($result->itemsFetched);
    expect(ActorActivitySummaryModel::query()->count())->toBeGreaterThan(0);
});

it('keeps repeated correlation batch processing idempotent', function (): void {
    seedMaterializationAuditEntry();

    $runner = app(ProjectionRefreshRunnerPort::class);
    $request = new ProjectionRefreshRequestDto(
        projectionFamily: ProjectionFamily::Correlation,
        archiveVisibilityTier: ArchiveVisibilityTier::ActiveOnly,
        projectionVersion: '1.0.0',
    );

    $runner->runBatch($request);

    $cursor = app(ProjectionCursorRepository::class)->findByFamilyAndTier(
        ProjectionFamily::Correlation,
        ArchiveVisibilityTier::ActiveOnly,
    );
    expect($cursor)->not->toBeNull();

    $countAfterFirst = CorrelationProjectionEntryModel::query()->count();
    expect($countAfterFirst)->toBeGreaterThan(0);

    ProjectionCursorModel::query()
        ->where('id', $cursor->id)
        ->update([
            'last_source_audit_log_id' => null,
            'last_occurred_at' => null,
        ]);

    $repeat = $runner->runBatch($request);

    expect($repeat->itemsMaterialized)->toBe(0);
    expect(CorrelationProjectionEntryModel::query()->count())->toBe($countAfterFirst);
});

it('advances cursor only after successful persistence', function (): void {
    seedMaterializationAuditEntry();

    $before = app(ProjectionCursorRepository::class)->findByFamilyAndTier(
        ProjectionFamily::Correlation,
        ArchiveVisibilityTier::ActiveOnly,
    );

    expect($before)->toBeNull();

    app(ProjectionRefreshRunnerPort::class)->runBatch(new ProjectionRefreshRequestDto(
        projectionFamily: ProjectionFamily::Correlation,
        archiveVisibilityTier: ArchiveVisibilityTier::ActiveOnly,
        projectionVersion: '1.0.0',
    ));

    $after = app(ProjectionCursorRepository::class)->findByFamilyAndTier(
        ProjectionFamily::Correlation,
        ArchiveVisibilityTier::ActiveOnly,
    );

    expect($after?->lastSourceAuditLogId)->not->toBeNull();
    expect($after?->refreshedAt)->not->toBeNull();
});

it('does not advance cursor when materialization fails', function (): void {
    seedMaterializationAuditEntry();

    $throwingMaterializer = new class implements ProjectionRefreshMaterializerPort
    {
        public function supports(ProjectionFamily $projectionFamily): bool
        {
            return $projectionFamily === ProjectionFamily::Correlation;
        }

        public function materialize(
            array $items,
            ArchiveVisibilityTier $archiveVisibilityTier,
            string $projectionVersion,
        ): int {
            throw new RuntimeException('materialization failed');
        }
    };

    app()->instance(
        ProjectionRefreshMaterializerRegistry::class,
        new ProjectionRefreshMaterializerRegistry([$throwingMaterializer]),
    );
    app()->forgetInstance(ProjectionRefreshRunnerPort::class);

    expect(fn () => app(ProjectionRefreshRunnerPort::class)->runBatch(new ProjectionRefreshRequestDto(
        projectionFamily: ProjectionFamily::Correlation,
        archiveVisibilityTier: ArchiveVisibilityTier::ActiveOnly,
        projectionVersion: '1.0.0',
    )))->toThrow(RuntimeException::class);

    $cursor = app(ProjectionCursorRepository::class)->findByFamilyAndTier(
        ProjectionFamily::Correlation,
        ArchiveVisibilityTier::ActiveOnly,
    );

    expect($cursor?->lastSourceAuditLogId)->toBeNull();
    expect($cursor?->status)->toBe(ProjectionCursorStatus::Failed);
    expect(CorrelationProjectionEntryModel::query()->count())->toBe(0);
});

it('materializes all authorized projection families for the same source audit batch', function (): void {
    seedMaterializationAuditEntry();

    runMaterializationBatch(ProjectionFamily::Correlation);
    runMaterializationBatch(ProjectionFamily::WindowAggregate);
    runMaterializationBatch(ProjectionFamily::ActorActivity);

    expect(CorrelationProjectionEntryModel::query()->count())->toBeGreaterThan(0);
    expect(AuditWindowAggregateModel::query()->count())->toBeGreaterThan(0);
    expect(ActorActivitySummaryModel::query()->count())->toBeGreaterThan(0);
});
