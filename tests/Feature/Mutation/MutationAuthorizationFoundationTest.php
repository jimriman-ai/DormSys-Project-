<?php

declare(strict_types=1);

use App\Application\Mutation\Exceptions\UnauthorizedMutationException;
use App\Application\Mutation\Registry\ExemptMutationActionRegistry;
use App\Application\Mutation\Registry\MutationCapabilityCatalog;
use App\Application\Mutation\Registry\PendingMutationAuthorizationRegistry;
use App\Application\Mutation\Registry\SystemMutationCapabilityRegistry;
use App\Application\Mutation\Services\MutationPolicyEnforcementPoint;
use App\Application\Mutation\Support\MutationPrincipalContext;
use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\Audit\Application\Services\RecordAuditAction;
use App\Modules\Identity\Application\Services\CreateUserAction;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use App\Shared\ValueObjects\SystemActorId;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(MutationPrincipalContextHolder::class)->clear();
    request()->attributes->remove('audit_principal_user_id');
    putenv('MUTATION_ACTING_PRINCIPAL');
});

it('fails closed when principal is missing for business mutation capability', function (): void {
    $mpep = app(MutationPolicyEnforcementPoint::class);

    expect(fn () => $mpep->enforce(MutationCapabilityCatalog::REQUEST_SUBMIT_OWN))
        ->toThrow(UnauthorizedMutationException::class, 'Mutation requires an authorized principal.');
});

it('allows business mutation capability when principal is present', function (): void {
    $principalId = UuidGenerator::uuid7();

    MutationPrincipalContext::runAs($principalId, function (): void {
        app(MutationPolicyEnforcementPoint::class)->enforce(MutationCapabilityCatalog::REQUEST_APPROVE);
    });

    expect(app(MutationPrincipalContextHolder::class)->get())->toBeNull();
});

it('resolves principal from http audit attribute', function (): void {
    $principalId = UuidGenerator::uuid7();
    request()->attributes->set('audit_principal_user_id', $principalId);

    app(MutationPolicyEnforcementPoint::class)->enforce(MutationCapabilityCatalog::REQUEST_SUBMIT_OWN);

    expect(app(MutationPrincipalContextHolder::class)->get())->toBeNull();
});

it('allows registered system capability only for system actor', function (): void {
    $mpep = app(MutationPolicyEnforcementPoint::class);

    expect(fn () => $mpep->enforce(SystemMutationCapabilityRegistry::FOUNDATION_SELF_TEST))
        ->toThrow(UnauthorizedMutationException::class, 'System mutation capability requires the system actor.');

    MutationPrincipalContext::runAs(SystemActorId::VALUE, function () use ($mpep): void {
        $mpep->enforce(SystemMutationCapabilityRegistry::FOUNDATION_SELF_TEST);
    });

    expect(app(MutationPrincipalContextHolder::class)->get())->toBeNull();
});

it('rejects system capability for non-system principals', function (): void {
    MutationPrincipalContext::runAs(UuidGenerator::uuid7(), function (): void {
        expect(fn () => app(MutationPolicyEnforcementPoint::class)->enforce(
            SystemMutationCapabilityRegistry::FOUNDATION_SELF_TEST,
        ))->toThrow(UnauthorizedMutationException::class, 'System mutation capability requires the system actor.');
    });
});

it('registers trusted internal audit write as exempt from mpep requirement', function (): void {
    expect(ExemptMutationActionRegistry::isExempt(RecordAuditAction::class))->toBeTrue();
});

it('registers existing business mutation actions as pending adoption', function (): void {
    expect(PendingMutationAuthorizationRegistry::isPending(CreateUserAction::class))->toBeTrue();
    expect(ExemptMutationActionRegistry::isExempt(CreateUserAction::class))->toBeFalse();
});

it('renders unauthorized mutation as forbidden json on api routes', function (): void {
    $request = request()->create('/api/example', 'POST');
    $request->headers->set('Accept', 'application/json');

    $response = app(Illuminate\Contracts\Debug\ExceptionHandler::class)->render(
        $request,
        new UnauthorizedMutationException('Mutation denied.'),
    );

    expect($response->getStatusCode())->toBe(403)
        ->and($response->getContent())->toContain('Mutation denied.');
});

it('resolves console acting principal from environment variable', function (): void {
    $principalId = UuidGenerator::uuid7();
    putenv('MUTATION_ACTING_PRINCIPAL='.$principalId);

    app(MutationPolicyEnforcementPoint::class)->enforce(MutationCapabilityCatalog::REQUEST_SUBMIT_OWN);

    expect(app(MutationPrincipalContextHolder::class)->get())->toBeNull();
});
