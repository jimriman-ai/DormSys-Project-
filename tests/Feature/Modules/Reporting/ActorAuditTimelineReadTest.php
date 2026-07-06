<?php

declare(strict_types=1);

use App\Modules\Audit\Application\Contracts\AuditRecordingContract;
use App\Modules\Audit\Application\DTOs\AuditEntryDto;
use App\Modules\Audit\Domain\Enums\ActorType;
use App\Modules\Audit\Domain\Enums\AuditEventType;
use App\Modules\Audit\Domain\Exceptions\UnauthorizedAuditAccessException;
use App\Modules\Audit\Infrastructure\Persistence\Models\AuditLogModel;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Modules\Reporting\Application\Contracts\ReportingReadContract;
use App\Modules\Reporting\Application\DTOs\ActorTimelineQuery;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['audit.sync_in_tests' => true]);
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);
});

/**
 * @param  array<string, mixed>  $overrides
 */
function seedActorTimelineAuditEntry(array $overrides = []): AuditLogModel
{
    $actorId = $overrides['actorId'] ?? UuidGenerator::uuid7();
    $correlationId = $overrides['correlationId'] ?? 'actor:timeline:seed:'.UuidGenerator::uuid7();

    $payload = array_merge([
        'correlationId' => $correlationId,
        'eventType' => AuditEventType::RequestApproved->value,
        'entityType' => 'request',
        'entityId' => UuidGenerator::uuid7(),
        'actorType' => ActorType::User->value,
        'actorId' => $actorId,
        'sourceContext' => 'request',
        'oldValues' => ['status' => 'pending'],
        'newValues' => ['status' => 'approved'],
        'occurredAt' => '2026-07-02T10:00:00Z',
    ], $overrides);

    $result = app(AuditRecordingContract::class)->record(AuditEntryDto::fromArray($payload));

    return AuditLogModel::query()->findOrFail($result->auditLogId);
}

function authenticateActorTimelineReader(string $role = IdentityRoleSeeder::ROLE_HR_MGR): UserModel
{
    $user = createIdentityUserThroughMutation('Actor Timeline Reader', 'actor-timeline-reader@example.com');
    assignRoleThroughMutation($user->requireId(), $role);
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    request()->attributes->set('audit_principal_user_id', $model->id);

    return $model;
}

it('returns actor audit timeline with T0 provenance for authorized readers', function (): void {
    authenticateActorTimelineReader();
    $actorId = UuidGenerator::uuid7();

    seedActorTimelineAuditEntry([
        'actorId' => $actorId,
        'correlationId' => 'actor:timeline:001',
        'occurredAt' => '2026-07-02T09:00:00Z',
    ]);
    seedActorTimelineAuditEntry([
        'actorId' => $actorId,
        'correlationId' => 'actor:timeline:002',
        'occurredAt' => '2026-07-02T11:00:00Z',
    ]);
    seedActorTimelineAuditEntry([
        'actorId' => UuidGenerator::uuid7(),
        'correlationId' => 'actor:timeline:other',
    ]);

    $result = app(ReportingReadContract::class)->actorTimeline(new ActorTimelineQuery(
        actorType: ActorType::User->value,
        actorId: $actorId,
    ));

    expect($result->total)->toBe(2);
    expect($result->items)->toHaveCount(2);
    expect($result->provenance->sourceTier)->toBe('T0');
    expect($result->summary->totalCount)->toBe(2);
    expect($result->items[0]->actorId)->toBe($actorId);
});

it('denies actor timeline access without audit.read permission', function (): void {
    $actorId = UuidGenerator::uuid7();
    seedActorTimelineAuditEntry(['actorId' => $actorId, 'correlationId' => 'actor:deny:001']);

    $user = createIdentityUserThroughMutation('No Actor Access', 'no-actor-access@example.com');
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    request()->attributes->set('audit_principal_user_id', $model->id);

    expect(fn () => app(ReportingReadContract::class)->actorTimeline(new ActorTimelineQuery(
        actorType: ActorType::User->value,
        actorId: $actorId,
    )))->toThrow(UnauthorizedAuditAccessException::class);
});

it('denies include archived for actor timeline without audit.read permission', function (): void {
    $user = createIdentityUserThroughMutation('No Actor Archive Access', 'no-actor-archive@example.com');
    $model = UserModel::query()->findOrFail($user->requireId()->value);
    request()->attributes->set('audit_principal_user_id', $model->id);

    $actorId = UuidGenerator::uuid7();

    seedActorTimelineAuditEntry(['actorId' => $actorId, 'correlationId' => 'actor:archived:001']);

    expect(fn () => app(ReportingReadContract::class)->actorTimeline(new ActorTimelineQuery(
        actorType: ActorType::User->value,
        actorId: $actorId,
        includeArchived: true,
    )))->toThrow(UnauthorizedAuditAccessException::class);
});
