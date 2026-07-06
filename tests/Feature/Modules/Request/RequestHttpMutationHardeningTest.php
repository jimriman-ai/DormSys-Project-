<?php

declare(strict_types=1);

use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Modules\Request\Application\Contracts\RequestRepositoryContract;
use App\Modules\Request\Application\Services\ApproveRequestStageAction;
use App\Modules\Request\Application\Services\SubmitRequestAction;
use App\Modules\Request\Domain\States\ApprovedState;
use App\Modules\Request\Domain\States\CancelledState;
use App\Modules\Request\Domain\States\DraftState;
use App\Modules\Request\Domain\States\PendingDepartmentManagerState;
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

/**
 * @return list<string>
 */
function requestMutationRouteMiddleware(string $routeName): array
{
    /** @var Router $router */
    $router = app('router');
    $route = $router->getRoutes()->getByName($routeName);

    if ($route === null) {
        throw new RuntimeException("Route [{$routeName}] is not registered.");
    }

    /** @var list<string> $middleware */
    $middleware = $route->gatherMiddleware();

    return $middleware;
}

describe('principal consistency', function (): void {
    it('rejects client-supplied approverId on approve', function (): void {
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

        $this->postJson(requestHttpMutationUrl($submitted['id'], 'approve'), [
            'approverId' => UuidGenerator::uuid7(),
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['approverId']);
    });

    it('rejects client-supplied approverId on reject', function (): void {
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
            'approverId' => UuidGenerator::uuid7(),
            'reason' => 'Policy mismatch',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['approverId']);
    });

    it('rejects client-supplied owner and actor identity fields on submit', function (): void {
        $actor = createRequestHttpMutationEmployee();
        $draft = createDraftPersonalRequestForHttp($actor['employee']);

        authenticateRequestHttpMutationUser($actor['identity']);

        $this->postJson(requestHttpMutationUrl($draft->requireId()->value, 'submit'), [
            'ownerId' => UuidGenerator::uuid7(),
            'actorId' => UuidGenerator::uuid7(),
            'employeeId' => UuidGenerator::uuid7(),
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['ownerId', 'actorId', 'employeeId']);
    });

    it('uses session principal even when a mismatched audit attribute was pre-set in tests', function (): void {
        $owner = createRequestHttpMutationEmployee();
        $other = createMutationApprover();
        $draft = createDraftPersonalRequestForHttp($owner['employee']);

        authenticateRequestHttpMutationUser($owner['identity']);
        request()->attributes->set('audit_principal_user_id', $other['principalId']);

        $this->postJson(requestHttpMutationUrl($draft->requireId()->value, 'submit'))
            ->assertOk()
            ->assertJsonPath('data.status', PendingDepartmentManagerState::$name);
    });

    it('fails cleanly when principal context is missing', function (): void {
        $actor = createRequestHttpMutationEmployee();
        $draft = createDraftPersonalRequestForHttp($actor['employee']);

        $this->postJson(requestHttpMutationUrl($draft->requireId()->value, 'submit'))
            ->assertUnauthorized()
            ->assertJsonPath('success', false);
    });
});

describe('route and middleware safety', function (): void {
    it('applies the same authenticated middleware stack to all mutation routes', function (): void {
        $required = ['auth:api', 'request.mutation.principal', 'audit.principal'];
        $routeNames = [
            'requests.mutations.submit',
            'requests.mutations.cancel',
            'requests.mutations.approve',
            'requests.mutations.reject',
        ];

        foreach ($routeNames as $routeName) {
            $middleware = requestMutationRouteMiddleware($routeName);

            foreach ($required as $entry) {
                expect($middleware)->toContain($entry);
            }
        }
    });

    it('does not expose unauthenticated alternate routes for request mutations', function (): void {
        /** @var Router $router */
        $router = app('router');

        foreach ($router->getRoutes()->getRoutes() as $route) {
            if (! str_starts_with($route->uri(), 'api/requests/')) {
                continue;
            }

            expect($route->gatherMiddleware())->toContain('auth:api');
        }
    });

    it('preserves unauthenticated session failure semantics', function (): void {
        $this->postJson(requestHttpMutationUrl(UuidGenerator::uuid7(), 'cancel'))
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');
    });
});

describe('replay and duplicate safety', function (): void {
    it('fails safely on repeated submit after the first valid transition', function (): void {
        $actor = createRequestHttpMutationEmployee();
        $draft = createDraftPersonalRequestForHttp($actor['employee']);

        authenticateRequestHttpMutationUser($actor['identity']);

        $this->postJson(requestHttpMutationUrl($draft->requireId()->value, 'submit'))
            ->assertOk();

        $this->postJson(requestHttpMutationUrl($draft->requireId()->value, 'submit'))
            ->assertServerError();

        $reloaded = app(RequestRepositoryContract::class)->findById($draft->requireId());
        expect($reloaded?->status)->toBe(PendingDepartmentManagerState::$name);
    });

    it('fails safely on repeated cancel after the first valid transition', function (): void {
        $actor = createRequestHttpMutationEmployee();
        $draft = createDraftPersonalRequestForHttp($actor['employee']);

        authenticateRequestHttpMutationUser($actor['identity']);

        $this->postJson(requestHttpMutationUrl($draft->requireId()->value, 'cancel'))
            ->assertOk();

        $this->postJson(requestHttpMutationUrl($draft->requireId()->value, 'cancel'))
            ->assertServerError();

        $reloaded = app(RequestRepositoryContract::class)->findById($draft->requireId());
        expect($reloaded?->status)->toBe(CancelledState::$name);
    });

    it('fails safely on repeated approve after the request is fully approved', function (): void {
        $owner = createRequestHttpMutationEmployee();
        $draft = createDraftPersonalRequestForHttp($owner['employee']);

        authenticateRequestHttpMutationUser($owner['identity']);
        $request = asRequestOwner($owner['employee'], fn () => app(SubmitRequestAction::class)->execute($draft->requireId()));

        for ($index = 0; $index < 4; $index++) {
            $request = asMutationApprover(fn ($approverId) => app(ApproveRequestStageAction::class)->execute(
                $request->requireId(),
                $approverId,
            ));
        }

        expect($request->status)->toBe(ApprovedState::$name);

        authenticateRequestHttpMutationUser(
            UserModel::query()->findOrFail(createMutationApprover()['principalId']),
        );

        $this->postJson(requestHttpMutationUrl($request->requireId()->value, 'approve'))
            ->assertServerError();

        $reloaded = app(RequestRepositoryContract::class)->findById($request->requireId());
        expect($reloaded?->status)->toBe(ApprovedState::$name);
    });

    it('fails safely on repeated reject after the first valid transition', function (): void {
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
        ])->assertOk();

        $this->postJson(requestHttpMutationUrl($submitted['id'], 'reject'), [
            'reason' => 'Duplicate reject attempt',
        ])->assertServerError();

        $reloaded = app(RequestRepositoryContract::class)->findById($draft->requireId());
        expect($reloaded?->status)->toBe(RejectedState::$name);
    });

    it('never changes state on repeated unauthorized submit attempts', function (): void {
        $owner = createRequestHttpMutationEmployee();
        $other = createRequestHttpMutationEmployee('0000000019');
        $draft = createDraftPersonalRequestForHttp($owner['employee']);

        authenticateRequestHttpMutationUser($other['identity']);

        $this->postJson(requestHttpMutationUrl($draft->requireId()->value, 'submit'))
            ->assertForbidden();
        $this->postJson(requestHttpMutationUrl($draft->requireId()->value, 'submit'))
            ->assertForbidden();

        $reloaded = app(RequestRepositoryContract::class)->findById($draft->requireId());
        expect($reloaded?->status)->toBe(DraftState::$name);
    });
});

describe('boundary integrity', function (): void {
    it('continues to invoke enforced application actions only', function (): void {
        $source = file_get_contents(app_path('Modules/Request/Presentation/Http/Controllers/RequestMutationController.php'));

        expect($source)->toContain('$this->submitRequest->execute')
            ->and($source)->toContain('$this->cancelRequest->execute')
            ->and($source)->toContain('$this->approveRequestStage->execute')
            ->and($source)->toContain('$this->rejectRequest->execute');
    });

    it('does not expose controller permission shortcuts', function (): void {
        $contents = file_get_contents(app_path('Modules/Request/Presentation/Http/Controllers/RequestMutationController.php'));

        expect($contents)->not->toContain('Gate::')
            ->and($contents)->not->toContain('RequestRepositoryContract')
            ->and($contents)->not->toContain('RequestMutationAuthorizationGate');
    });

    it('does not expose repository or direct mutation paths at the http layer', function (): void {
        $contents = file_get_contents(app_path('Modules/Request/Presentation/Http/Controllers/RequestMutationController.php'));

        expect($contents)->not->toContain('->save(')
            ->and($contents)->not->toContain('DB::transaction');
    });

    it('preserves unauthorized mutation failure via UnauthorizedMutationException semantics', function (): void {
        $owner = createRequestHttpMutationEmployee();
        $other = createRequestHttpMutationEmployee('0000000019');
        $draft = createDraftPersonalRequestForHttp($owner['employee']);

        authenticateRequestHttpMutationUser($other['identity']);

        $this->postJson(requestHttpMutationUrl($draft->requireId()->value, 'cancel'))
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Mutation actor must own the request.');
    });
});
