<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Audit\Application\Contracts\AuditRecordingContract;
use App\Modules\Audit\Application\DTOs\AuditEntryDto;
use App\Modules\Audit\Domain\Enums\ActorType;
use App\Modules\Audit\Domain\Enums\AuditEventType;
use App\Modules\Identity\Application\Services\AssignRoleToUserAction;
use App\Modules\Identity\Application\Services\CreateUserAction;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['audit.sync_in_tests' => true]);
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);
});

function createApiSessionCredentialPair(
    string $email = 'api-session@example.com',
    string $password = 'secret-password',
    string $role = IdentityRoleSeeder::ROLE_ADMINISTRATOR,
): UserModel {
    User::factory()->create([
        'email' => $email,
        'password' => $password,
    ]);

    $identityUser = app(CreateUserAction::class)->execute('API Session User', $email);
    app(AssignRoleToUserAction::class)->execute($identityUser->requireId(), $role);

    return UserModel::query()->findOrFail($identityUser->requireId()->value);
}

function seedApiSessionReportingAuditEntry(): string
{
    $entityId = UuidGenerator::uuid7();

    app(AuditRecordingContract::class)->record(AuditEntryDto::fromArray([
        'correlationId' => 'reporting:api-session:'.UuidGenerator::uuid7(),
        'eventType' => AuditEventType::RequestApproved->value,
        'entityType' => 'request',
        'entityId' => $entityId,
        'actorType' => ActorType::User->value,
        'actorId' => UuidGenerator::uuid7(),
        'sourceContext' => 'request',
        'occurredAt' => '2026-07-02T10:00:00Z',
    ]));

    return $entityId;
}

it('returns session cookie on valid credentials with identity match', function (): void {
    createApiSessionCredentialPair();

    $response = $this->postJson('/api/auth/login', [
        'identifier' => 'api-session@example.com',
        'password' => 'secret-password',
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true);

    $this->assertAuthenticated('api');
});

it('returns 401 on invalid credentials', function (): void {
    createApiSessionCredentialPair();

    $response = $this->postJson('/api/auth/login', [
        'identifier' => 'api-session@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertUnauthorized()
        ->assertJsonPath('success', false)
        ->assertJsonPath('failureReason', 'invalid_credentials');

    $this->assertGuest('api');
});

it('returns 403 on valid credentials without identity match', function (): void {
    User::factory()->create([
        'email' => 'credentials-only@example.com',
        'password' => 'secret-password',
    ]);

    $response = $this->postJson('/api/auth/login', [
        'identifier' => 'credentials-only@example.com',
        'password' => 'secret-password',
    ]);

    $response->assertForbidden()
        ->assertJsonPath('success', false);

    $this->assertGuest('api')
        ->assertGuest();
});

it('allows reporting access after session login', function (): void {
    createApiSessionCredentialPair();
    $entityId = seedApiSessionReportingAuditEntry();

    $this->postJson('/api/auth/login', [
        'identifier' => 'api-session@example.com',
        'password' => 'secret-password',
    ])->assertOk();

    $response = $this->getJson('/api/reporting/entity-timeline?'.http_build_query([
        'entityType' => 'request',
        'entityId' => $entityId,
    ]));

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('ru', 'RU-01');
});

it('still rejects unauthenticated reporting access', function (): void {
    $response = $this->getJson('/api/reporting/entity-timeline?'.http_build_query([
        'entityType' => 'request',
        'entityId' => UuidGenerator::uuid7(),
    ]));

    $response->assertUnauthorized()
        ->assertJsonPath('success', false);
});

it('clears session on logout', function (): void {
    createApiSessionCredentialPair();

    $this->postJson('/api/auth/login', [
        'identifier' => 'api-session@example.com',
        'password' => 'secret-password',
    ])->assertOk();

    $this->postJson('/api/auth/logout')
        ->assertOk()
        ->assertJsonPath('success', true);

    $this->assertGuest('api');

    $this->getJson('/api/reporting/entity-timeline?'.http_build_query([
        'entityType' => 'request',
        'entityId' => UuidGenerator::uuid7(),
    ]))->assertUnauthorized();
});
