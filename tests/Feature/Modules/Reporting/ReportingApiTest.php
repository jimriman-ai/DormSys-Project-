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
use App\Modules\Reporting\Application\DTOs\ProjectionRefreshRequestDto;
use App\Modules\Reporting\Domain\Enums\ArchiveVisibilityTier;
use App\Modules\Reporting\Domain\Enums\ProjectionFamily;
use App\Modules\Reporting\Domain\Enums\WindowGranularity;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['audit.sync_in_tests' => true]);
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);
});

function authenticateReportingApiReader(string $role = IdentityRoleSeeder::ROLE_ADMINISTRATOR): UserModel
{
    $user = app(CreateUserAction::class)->execute('Reporting API Reader', 'reporting-api@example.com');
    app(AssignRoleToUserAction::class)->execute($user->requireId(), $role);
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    test()->actingAs($model, 'api');
    request()->attributes->set('audit_principal_user_id', $model->id);

    return $model;
}

function seedReportingApiAuditEntry(array $overrides = []): string
{
    $entityId = $overrides['entityId'] ?? UuidGenerator::uuid7();

    app(AuditRecordingContract::class)->record(AuditEntryDto::fromArray(array_merge([
        'correlationId' => $overrides['correlationId'] ?? 'reporting:api:'.UuidGenerator::uuid7(),
        'eventType' => AuditEventType::RequestApproved->value,
        'entityType' => 'request',
        'entityId' => $entityId,
        'actorType' => ActorType::User->value,
        'actorId' => UuidGenerator::uuid7(),
        'sourceContext' => 'request',
        'occurredAt' => '2026-07-02T10:00:00Z',
    ], $overrides)));

    return $entityId;
}

function materializeReportingApiProjections(): void
{
    $runner = app(ProjectionRefreshRunnerPort::class);

    foreach ([ProjectionFamily::Correlation, ProjectionFamily::WindowAggregate, ProjectionFamily::ActorActivity] as $family) {
        $runner->runBatch(new ProjectionRefreshRequestDto(
            projectionFamily: $family,
            archiveVisibilityTier: ArchiveVisibilityTier::ActiveOnly,
            projectionVersion: '1.0.0',
        ));
    }
}

it('exposes RU-01 entity timeline endpoint with provenance envelope', function (): void {
    authenticateReportingApiReader();
    $entityId = seedReportingApiAuditEntry();

    $response = $this->getJson('/api/reporting/entity-timeline?'.http_build_query([
        'entityType' => 'request',
        'entityId' => $entityId,
    ]));

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('ru', 'RU-01')
        ->assertJsonPath('provenance.sourceTier', 'T0')
        ->assertJsonPath('provenance.filterHash', fn ($value) => is_string($value) && $value !== '')
        ->assertJsonStructure(['timestamp', 'data' => ['items', 'total', 'summary']]);
});

it('exposes RU-02 correlation bundle endpoint', function (): void {
    authenticateReportingApiReader();
    $correlationId = 'reporting:api:ru02';
    seedReportingApiAuditEntry(['correlationId' => $correlationId]);
    materializeReportingApiProjections();

    $response = $this->getJson('/api/reporting/correlation-bundle?'.http_build_query([
        'correlationId' => $correlationId,
    ]));

    $response->assertOk()
        ->assertJsonPath('ru', 'RU-02')
        ->assertJsonPath('provenance.sourceTier', 'T1');
});

it('exposes RU-03 audit window summary endpoint', function (): void {
    authenticateReportingApiReader();
    seedReportingApiAuditEntry(['occurredAt' => '2026-07-01T10:00:00+00:00']);
    materializeReportingApiProjections();

    $response = $this->getJson('/api/reporting/audit-window-summary?'.http_build_query([
        'windowStart' => '2026-07-01T00:00:00Z',
        'windowEnd' => '2026-07-02T00:00:00Z',
        'granularity' => WindowGranularity::Day->value,
    ]));

    $response->assertOk()
        ->assertJsonPath('ru', 'RU-03')
        ->assertJsonPath('provenance.sourceTier', 'T1');
});

it('exposes RU-04 compliance export endpoint', function (): void {
    authenticateReportingApiReader();
    seedReportingApiAuditEntry(['occurredAt' => '2026-07-01T10:00:00+00:00']);
    materializeReportingApiProjections();

    $response = $this->getJson('/api/reporting/compliance-export?'.http_build_query([
        'windowStart' => '2026-07-01T00:00:00Z',
        'windowEnd' => '2026-07-02T00:00:00Z',
        'granularity' => WindowGranularity::Day->value,
    ]));

    $response->assertOk()
        ->assertJsonPath('ru', 'RU-04')
        ->assertJsonPath('provenance.sourceTier', 'mixed');
});

it('exposes RU-05 security actor activity endpoint', function (): void {
    $user = authenticateReportingApiReader();
    $actorId = UuidGenerator::uuid7();
    seedReportingApiAuditEntry([
        'actorId' => $actorId,
        'occurredAt' => '2026-07-01T10:00:00+00:00',
    ]);
    materializeReportingApiProjections();

    $response = $this->getJson('/api/reporting/security-actor-activity?'.http_build_query([
        'actorType' => ActorType::User->value,
        'actorId' => $actorId,
        'windowStart' => '2026-07-01T00:00:00Z',
        'windowEnd' => '2026-07-02T00:00:00Z',
        'granularity' => WindowGranularity::Day->value,
    ]));

    $response->assertOk()
        ->assertJsonPath('ru', 'RU-05')
        ->assertJsonPath('provenance.sourceTier', 'T1');
});

it('exposes RU-06 drill-down endpoint', function (): void {
    authenticateReportingApiReader();
    $entityId = seedReportingApiAuditEntry();

    $response = $this->getJson('/api/reporting/drill-down?'.http_build_query([
        'entityType' => 'request',
        'entityId' => $entityId,
    ]));

    $response->assertOk()
        ->assertJsonPath('ru', 'RU-06')
        ->assertJsonPath('provenance.sourceTier', 'T0');
});

it('returns 401 for unauthenticated reporting api access', function (): void {
    $response = $this->getJson('/api/reporting/entity-timeline?'.http_build_query([
        'entityType' => 'request',
        'entityId' => UuidGenerator::uuid7(),
    ]));

    $response->assertUnauthorized();
});

it('returns 403 for authenticated user without audit.read permission', function (): void {
    $user = app(CreateUserAction::class)->execute('No Audit Read', 'no-audit-read@example.com');
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    $this->actingAs($model, 'api');

    $response = $this->getJson('/api/reporting/entity-timeline?'.http_build_query([
        'entityType' => 'request',
        'entityId' => UuidGenerator::uuid7(),
    ]));

    $response->assertForbidden()
        ->assertJsonPath('success', false);
});

it('returns 422 for invalid reporting api query parameters', function (): void {
    authenticateReportingApiReader();

    $response = $this->getJson('/api/reporting/entity-timeline?entityType=request');

    $response->assertUnprocessable()
        ->assertJsonPath('success', false);
});

it('registers all six reporting api endpoints', function (): void {
    $routes = collect(app('router')->getRoutes())
        ->filter(static fn ($route) => str_starts_with($route->uri(), 'api/reporting/'))
        ->map(static fn ($route) => $route->uri())
        ->values()
        ->all();

    expect($routes)->toContain(
        'api/reporting/entity-timeline',
        'api/reporting/correlation-bundle',
        'api/reporting/audit-window-summary',
        'api/reporting/compliance-export',
        'api/reporting/security-actor-activity',
        'api/reporting/drill-down',
    );
});
