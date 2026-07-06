<?php

declare(strict_types=1);

use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Modules\Request\Application\Contracts\RequestRepositoryContract;
use App\Modules\Request\Application\Services\ApproveRequestStageAction;
use App\Modules\Request\Application\Services\CancelRequestAction;
use App\Modules\Request\Application\Services\RejectRequestAction;
use App\Modules\Request\Application\Services\SubmitRequestAction;
use App\Modules\Request\Domain\States\CancelledState;
use App\Modules\Request\Domain\States\DraftState;
use App\Modules\Request\Domain\States\PendingDepartmentManagerState;
use App\Modules\Request\Domain\States\PendingHRState;
use App\Modules\Request\Domain\States\RejectedState;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Router;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Carbon::setTestNow('2026-06-23 12:00:00');
    app(MutationPrincipalContextHolder::class)->clear();
    request()->attributes->remove('audit_principal_user_id');
});

afterEach(function (): void {
    Carbon::setTestNow();
});

describe('authentication / session', function (): void {
    it('rejects unauthenticated submit', function (): void {
        $actor = createRequestHttpMutationEmployee();
        $draft = createDraftPersonalRequestForHttp($actor['employee']);

        $this->postJson(requestHttpMutationUrl($draft->requireId()->value, 'submit'))
            ->assertUnauthorized()
            ->assertJsonPath('success', false);
    });

    it('rejects unauthenticated cancel', function (): void {
        $actor = createRequestHttpMutationEmployee();
        $draft = createDraftPersonalRequestForHttp($actor['employee']);

        $this->postJson(requestHttpMutationUrl($draft->requireId()->value, 'cancel'))
            ->assertUnauthorized()
            ->assertJsonPath('success', false);
    });

    it('rejects unauthenticated approve', function (): void {
        $this->postJson(requestHttpMutationUrl(UuidGenerator::uuid7(), 'approve'))
            ->assertUnauthorized()
            ->assertJsonPath('success', false);
    });

    it('rejects unauthenticated reject', function (): void {
        $this->postJson(requestHttpMutationUrl(UuidGenerator::uuid7(), 'reject'), [
            'reason' => 'Not eligible',
        ])->assertUnauthorized()
            ->assertJsonPath('success', false);
    });
});

describe('authorization', function (): void {
    it('denies submit when authenticated principal does not own the request', function (): void {
        $owner = createRequestHttpMutationEmployee();
        $other = createRequestHttpMutationEmployee('0000000019');
        $draft = createDraftPersonalRequestForHttp($owner['employee']);

        authenticateRequestHttpMutationUser($other['identity']);

        $this->postJson(requestHttpMutationUrl($draft->requireId()->value, 'submit'))
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Mutation actor must own the request.');

        $reloaded = app(RequestRepositoryContract::class)->findById($draft->requireId());
        expect($reloaded?->status)->toBe(DraftState::$name);
    });

    it('denies cancel when authenticated principal does not own the request', function (): void {
        $owner = createRequestHttpMutationEmployee();
        $other = createRequestHttpMutationEmployee('0000000019');
        $draft = createDraftPersonalRequestForHttp($owner['employee']);

        authenticateRequestHttpMutationUser($other['identity']);

        $this->postJson(requestHttpMutationUrl($draft->requireId()->value, 'cancel'))
            ->assertForbidden()
            ->assertJsonPath('message', 'Mutation actor must own the request.');

        $reloaded = app(RequestRepositoryContract::class)->findById($draft->requireId());
        expect($reloaded?->status)->toBe(DraftState::$name);
    });

    it('denies approve when request is not awaiting approval at the current stage', function (): void {
        $actor = createRequestHttpMutationEmployee();
        $draft = createDraftPersonalRequestForHttp($actor['employee']);

        authenticateRequestHttpMutationUser($actor['identity']);

        $this->postJson(requestHttpMutationUrl($draft->requireId()->value, 'approve'))
            ->assertConflict()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Request is not awaiting approval.');

        $reloaded = app(RequestRepositoryContract::class)->findById($draft->requireId());
        expect($reloaded?->status)->toBe(DraftState::$name);
    });

    it('denies reject when request is not awaiting approval at the current stage', function (): void {
        $actor = createRequestHttpMutationEmployee();
        $draft = createDraftPersonalRequestForHttp($actor['employee']);

        authenticateRequestHttpMutationUser($actor['identity']);

        $this->postJson(requestHttpMutationUrl($draft->requireId()->value, 'reject'), [
            'reason' => 'Insufficient documentation',
        ])->assertConflict()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Request is not awaiting approval.');

        $reloaded = app(RequestRepositoryContract::class)->findById($draft->requireId());
        expect($reloaded?->status)->toBe(DraftState::$name);
    });
});

describe('happy path', function (): void {
    it('allows authenticated owner to submit own request', function (): void {
        $actor = createRequestHttpMutationEmployee();
        $draft = createDraftPersonalRequestForHttp($actor['employee']);

        authenticateRequestHttpMutationUser($actor['identity']);

        $this->postJson(requestHttpMutationUrl($draft->requireId()->value, 'submit'))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', PendingDepartmentManagerState::$name)
            ->assertJsonPath('data.id', $draft->requireId()->value);
    });

    it('allows authenticated owner to cancel own eligible request', function (): void {
        $actor = createRequestHttpMutationEmployee();
        $draft = createDraftPersonalRequestForHttp($actor['employee']);

        authenticateRequestHttpMutationUser($actor['identity']);

        $this->postJson(requestHttpMutationUrl($draft->requireId()->value, 'cancel'))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', CancelledState::$name);
    });

    it('allows authenticated valid approver to approve current stage', function (): void {
        $owner = createRequestHttpMutationEmployee();
        $approver = createMutationApprover();
        $draft = createDraftPersonalRequestForHttp($owner['employee']);

        authenticateRequestHttpMutationUser($owner['identity']);

        $submitted = $this->postJson(requestHttpMutationUrl($draft->requireId()->value, 'submit'))
            ->assertOk()
            ->json('data');

        authenticateRequestHttpMutationUser(
            UserModel::query()->findOrFail($approver['principalId']),
        );

        $this->postJson(requestHttpMutationUrl($submitted['id'], 'approve'))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', PendingHRState::$name);
    });

    it('allows authenticated valid approver to reject current stage', function (): void {
        $owner = createRequestHttpMutationEmployee();
        $approver = createMutationApprover();
        $draft = createDraftPersonalRequestForHttp($owner['employee']);

        authenticateRequestHttpMutationUser($owner['identity']);

        $submitted = $this->postJson(requestHttpMutationUrl($draft->requireId()->value, 'submit'))
            ->assertOk()
            ->json('data');

        authenticateRequestHttpMutationUser(
            UserModel::query()->findOrFail($approver['principalId']),
        );

        $this->postJson(requestHttpMutationUrl($submitted['id'], 'reject'), [
            'reason' => 'Does not meet policy',
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', RejectedState::$name)
            ->assertJsonPath('data.rejectionReason', 'Does not meet policy');
    });
});

describe('transport / validation', function (): void {
    it('returns not found for malformed request route id', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateRequestHttpMutationUser($actor['identity']);

        $this->postJson('/api/requests/not-a-uuid/submit')
            ->assertNotFound();
    });

    it('returns validation failure when reject reason is missing', function (): void {
        $owner = createRequestHttpMutationEmployee();
        $draft = createDraftPersonalRequestForHttp($owner['employee']);

        authenticateRequestHttpMutationUser($owner['identity']);

        $submitted = $this->postJson(requestHttpMutationUrl($draft->requireId()->value, 'submit'))
            ->assertOk()
            ->json('data');

        $approver = createMutationApprover();
        authenticateRequestHttpMutationUser(
            UserModel::query()->findOrFail($approver['principalId']),
        );

        $this->postJson(requestHttpMutationUrl($submitted['id'], 'reject'), [])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed.')
            ->assertJsonValidationErrors(['reason']);
    });
});

describe('boundary / anti-bypass', function (): void {
    it('wires submit endpoint to SubmitRequestAction execute path', function (): void {
        $source = file_get_contents(app_path('Modules/Request/Presentation/Http/Controllers/RequestMutationController.php'));

        expect($source)->toContain(SubmitRequestAction::class)
            ->and($source)->toContain('$this->submitRequest->execute');
    });

    it('wires cancel endpoint to CancelRequestAction execute path', function (): void {
        $source = file_get_contents(app_path('Modules/Request/Presentation/Http/Controllers/RequestMutationController.php'));

        expect($source)->toContain(CancelRequestAction::class)
            ->and($source)->toContain('$this->cancelRequest->execute');
    });

    it('wires approve endpoint to ApproveRequestStageAction execute path', function (): void {
        $source = file_get_contents(app_path('Modules/Request/Presentation/Http/Controllers/RequestMutationController.php'));

        expect($source)->toContain(ApproveRequestStageAction::class)
            ->and($source)->toContain('$this->approveRequestStage->execute');
    });

    it('wires reject endpoint to RejectRequestStageAction execute path', function (): void {
        $source = file_get_contents(app_path('Modules/Request/Presentation/Http/Controllers/RequestMutationController.php'));
        expect($source)->toContain(RejectRequestAction::class)
            ->and($source)->toContain('$this->rejectRequest->execute');
    });

    it('does not implement controller-level permission shortcuts', function (): void {
        $contents = file_get_contents(app_path('Modules/Request/Presentation/Http/Controllers/RequestMutationController.php'));

        expect($contents)->not->toContain('Gate::')
            ->and($contents)->not->toContain('->can(')
            ->and($contents)->not->toContain('hasRole(')
            ->and($contents)->not->toContain('RequestRepositoryContract')
            ->and($contents)->not->toContain('ApprovalStageResolver')
            ->and($contents)->not->toContain('RequestMutationAuthorizationGate');
    });

    it('preserves session login path for request mutation access', function (): void {
        $actor = createRequestHttpMutationEmployee();
        $draft = createDraftPersonalRequestForHttp($actor['employee']);

        $this->postJson('/api/auth/login', [
            'identifier' => $actor['email'],
            'password' => $actor['password'],
        ])->assertOk();

        $this->postJson(requestHttpMutationUrl($draft->requireId()->value, 'submit'))
            ->assertOk()
            ->assertJsonPath('data.status', PendingDepartmentManagerState::$name);
    });
});

it('registers request mutation routes on the authenticated api surface', function (): void {
    /** @var Router $router */
    $router = app('router');
    $routes = [];

    foreach ($router->getRoutes()->getRoutes() as $route) {
        if (str_starts_with($route->uri(), 'api/requests/')) {
            $routes[] = $route->uri();
        }
    }

    expect($routes)->toContain('api/requests/personal')
        ->and($routes)->toContain('api/requests/mine')
        ->and($routes)->toContain('api/requests/{requestId}')
        ->and($routes)->toContain('api/requests/{requestId}/submit')
        ->and($routes)->toContain('api/requests/{requestId}/cancel')
        ->and($routes)->toContain('api/requests/{requestId}/approve')
        ->and($routes)->toContain('api/requests/{requestId}/reject');
});
