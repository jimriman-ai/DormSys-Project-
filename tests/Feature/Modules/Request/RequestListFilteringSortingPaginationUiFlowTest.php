<?php

declare(strict_types=1);

use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\Request\Application\Contracts\RequestReadContract;
use App\Modules\Request\Application\DTOs\RequestEmployeeListFilterOptions;
use App\Modules\Request\Domain\States\DraftState;
use App\Modules\Request\Domain\States\PendingDepartmentManagerState;
use App\Modules\Request\Infrastructure\Persistence\Models\RequestModel;
use App\Modules\Request\Presentation\Livewire\RequestListPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

require_once __DIR__.'/support/http-mutation.php';

beforeEach(function (): void {
    Carbon::setTestNow('2026-06-23 12:00:00');
    app(MutationPrincipalContextHolder::class)->clear();
    request()->attributes->remove('audit_principal_user_id');

    $manifestPath = public_path('build/manifest.json');

    if (! is_file($manifestPath)) {
        if (! is_dir(dirname($manifestPath))) {
            mkdir(dirname($manifestPath), 0777, true);
        }

        file_put_contents($manifestPath, json_encode([
            'resources/css/app.css' => [
                'file' => 'assets/app.css',
                'src' => 'resources/css/app.css',
                'isEntry' => true,
            ],
            'resources/js/app.js' => [
                'file' => 'assets/app.js',
                'src' => 'resources/js/app.js',
                'isEntry' => true,
            ],
        ], JSON_THROW_ON_ERROR));
    }
});

afterEach(function (): void {
    Carbon::setTestNow();
});

function authenticateRequestListP4User(App\Modules\Identity\Infrastructure\Persistence\Models\UserModel $identity): void
{
    authenticateRequestHttpMutationUser($identity);
    app(MutationPrincipalContextHolder::class)->set($identity->id);
}

function createDraftRequestsForListP4(App\Modules\Employee\Domain\Entities\Employee $employee, int $count): void
{
    for ($index = 0; $index < $count; $index++) {
        Carbon::setTestNow(sprintf('2026-06-23 %02d:00:00', 8 + ($index % 12)));
        createDraftPersonalRequestForHttp($employee);
    }

    Carbon::setTestNow('2026-06-23 12:00:00');
}

describe('request list filtering sorting pagination ui flow', function (): void {
    it('exposes backend-supplied status filter options independent of visible rows', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateRequestListP4User($actor['identity']);
        createDraftPersonalRequestForHttp($actor['employee']);

        $component = Livewire::actingAs($actor['identity'], 'api')
            ->test(RequestListPage::class)
            ->call('refreshList')
            ->assertSet('uiState', 'ready')
            ->assertSee('فیلتر')
            ->assertSee('مرتب‌سازی', escape: false);

        $options = $component->get('statusOptions');

        expect($options)->toBe(RequestEmployeeListFilterOptions::statusValues())
            ->and($options)->toContain('approved')
            ->and($component->get('requests'))->toHaveCount(1)
            ->and(collect($component->get('requests'))->pluck('status')->unique()->all())->toBe([DraftState::$name]);
    });

    it('filters employee requests by exact status match', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateRequestListP4User($actor['identity']);

        $draft = createDraftPersonalRequestForHttp($actor['employee']);
        $pending = createDraftPersonalRequestForHttp($actor['employee']);

        RequestModel::query()
            ->whereKey($pending->requireId()->value)
            ->update(['status' => PendingDepartmentManagerState::$name]);

        Livewire::actingAs($actor['identity'], 'api')
            ->test(RequestListPage::class)
            ->set('statusFilter', DraftState::$name)
            ->call('refreshList')
            ->assertSet('page', 1)
            ->assertSet('uiState', 'ready')
            ->assertCount('requests', 1)
            ->assertSet('requests.0.id', $draft->requireId()->value)
            ->assertSet('requests.0.status', DraftState::$name);

        Livewire::actingAs($actor['identity'], 'api')
            ->test(RequestListPage::class)
            ->set('statusFilter', PendingDepartmentManagerState::$name)
            ->call('refreshList')
            ->assertCount('requests', 1)
            ->assertSet('requests.0.id', $pending->requireId()->value)
            ->assertSet('requests.0.status', PendingDepartmentManagerState::$name);
    });

    it('paginates employee requests at fifteen rows per page', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateRequestListP4User($actor['identity']);
        createDraftRequestsForListP4($actor['employee'], 16);

        Livewire::actingAs($actor['identity'], 'api')
            ->test(RequestListPage::class)
            ->call('refreshList')
            ->assertSet('total', 16)
            ->assertSet('lastPage', 2)
            ->assertSet('perPage', 15)
            ->assertCount('requests', 15)
            ->call('goToPage', 2)
            ->assertSet('page', 2)
            ->assertCount('requests', 1);
    });

    it('resets page to one when filter or sort changes', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateRequestListP4User($actor['identity']);
        createDraftRequestsForListP4($actor['employee'], 16);

        Livewire::actingAs($actor['identity'], 'api')
            ->test(RequestListPage::class)
            ->call('refreshList')
            ->call('goToPage', 2)
            ->assertSet('page', 2)
            ->set('statusFilter', DraftState::$name)
            ->assertSet('page', 1)
            ->call('refreshList')
            ->call('goToPage', 2)
            ->set('sortField', 'code')
            ->assertSet('page', 1);
    });

    it('sorts requests by selected field using backend ordering', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateRequestListP4User($actor['identity']);

        $first = createDraftPersonalRequestForHttp($actor['employee']);
        $second = createDraftPersonalRequestForHttp($actor['employee']);
        $third = createDraftPersonalRequestForHttp($actor['employee']);

        RequestModel::query()->whereKey($first->requireId()->value)->update(['code' => 'REQ-Z-999']);
        RequestModel::query()->whereKey($second->requireId()->value)->update(['code' => 'REQ-A-001']);
        RequestModel::query()->whereKey($third->requireId()->value)->update(['code' => 'REQ-M-500']);

        Livewire::actingAs($actor['identity'], 'api')
            ->test(RequestListPage::class)
            ->set('sortField', 'code')
            ->set('sortDirection', 'asc')
            ->call('refreshList')
            ->assertSet('requests.0.code', 'REQ-A-001')
            ->assertSet('requests.1.code', 'REQ-M-500')
            ->assertSet('requests.2.code', 'REQ-Z-999');
    });

    it('restores list state from url query parameters', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateRequestListP4User($actor['identity']);
        createDraftRequestsForListP4($actor['employee'], 3);

        Livewire::withQueryParams([
            'status' => DraftState::$name,
            'sort' => 'code',
            'dir' => 'asc',
            'page' => '1',
        ])
            ->actingAs($actor['identity'], 'api')
            ->test(RequestListPage::class)
            ->call('refreshList')
            ->assertSet('statusFilter', DraftState::$name)
            ->assertSet('sortField', 'code')
            ->assertSet('sortDirection', 'asc')
            ->assertSet('page', 1);
    });

    it('renders global empty state only without an active status filter', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateRequestListP4User($actor['identity']);

        Livewire::actingAs($actor['identity'], 'api')
            ->test(RequestListPage::class)
            ->call('refreshList')
            ->assertSet('uiState', 'empty')
            ->assertSee('درخواستی ثبت نشده است')
            ->assertDontSee('نتیجه‌ای یافت نشد');
    });

    it('renders filtered empty state with clear filter action', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateRequestListP4User($actor['identity']);
        createDraftPersonalRequestForHttp($actor['employee']);

        Livewire::actingAs($actor['identity'], 'api')
            ->test(RequestListPage::class)
            ->set('statusFilter', PendingDepartmentManagerState::$name)
            ->call('refreshList')
            ->assertSet('uiState', 'ready')
            ->assertSet('total', 0)
            ->assertSee('نتیجه‌ای یافت نشد')
            ->assertSee('حذف فیلتر')
            ->call('clearFilters')
            ->assertSet('statusFilter', null)
            ->assertSet('sortField', 'submitted_at')
            ->assertSet('sortDirection', 'desc')
            ->assertSet('page', 1)
            ->assertCount('requests', 1);
    });

    it('keeps listByEmployee and indexMine full-list behavior unchanged', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateRequestListP4User($actor['identity']);
        createDraftRequestsForListP4($actor['employee'], 16);

        $employeeId = $actor['employee']->requireId()->value;
        $fullList = app(RequestReadContract::class)->listByEmployee($employeeId);

        expect($fullList)->toHaveCount(16);

        $this->getJson('/api/requests/mine')
            ->assertOk()
            ->assertJsonCount(16, 'data');
    });
});
