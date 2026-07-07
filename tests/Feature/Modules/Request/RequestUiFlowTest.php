<?php

declare(strict_types=1);

use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\Request\Domain\States\DraftState;
use App\Modules\Request\Domain\States\PendingDepartmentManagerState;
use App\Modules\Request\Presentation\Livewire\RequestCreatePage;
use App\Modules\Request\Presentation\Livewire\RequestShowPage;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

require_once __DIR__.'/support/http-mutation.php';

function authenticateRequestUiUser(App\Modules\Identity\Infrastructure\Persistence\Models\UserModel $identity): void
{
    authenticateRequestHttpMutationUser($identity);
    app(MutationPrincipalContextHolder::class)->set($identity->id);
}

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

describe('request ui access', function (): void {
    it('redirects guests from the request list', function (): void {
        $this->get('/requests')->assertRedirect('/login');
    });

    it('renders the authenticated request list page', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateRequestUiUser($actor['identity']);

        $this->get('/requests')
            ->assertOk()
            ->assertSee('درخواست‌های من')
            ->assertSee('ثبت درخواست جدید');
    });

    it('renders the request create page', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateRequestUiUser($actor['identity']);

        $this->get('/requests/create')
            ->assertOk()
            ->assertSee('ثبت درخواست شخصی');
    });
});

describe('request ui flows', function (): void {
    it('creates a request through the ui and shows it on the list', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateRequestUiUser($actor['identity']);

        $dormitoryId = UuidGenerator::uuid7();

        Livewire::actingAs($actor['identity'], 'api')
            ->test(RequestCreatePage::class)
            ->set('dormitoryId', $dormitoryId)
            ->set('checkInDate', '2026-07-01')
            ->set('checkOutDate', '2026-12-31')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $this->get('/requests')
            ->assertOk()
            ->assertSee($dormitoryId);
    });

    it('shows backend validation feedback on create', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateRequestUiUser($actor['identity']);

        Livewire::actingAs($actor['identity'], 'api')
            ->test(RequestCreatePage::class)
            ->set('dormitoryId', 'not-a-uuid')
            ->set('checkInDate', '2026-07-01')
            ->set('checkOutDate', '2026-12-31')
            ->call('save')
            ->assertHasErrors(['dormitoryId']);
    });

    it('submits a draft request and reflects backend status', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateRequestUiUser($actor['identity']);

        $draft = createDraftPersonalRequestForHttp($actor['employee']);

        Livewire::actingAs($actor['identity'], 'api')
            ->test(RequestShowPage::class, ['requestId' => $draft->requireId()->value])
            ->assertSet('summary.status', DraftState::$name)
            ->call('submit')
            ->assertSet('summary.status', PendingDepartmentManagerState::$name)
            ->assertSet('actionError', null);
    });

    it('surfaces backend conflict without rewriting the message', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateRequestUiUser($actor['identity']);

        $draft = createDraftPersonalRequestForHttp($actor['employee']);

        $component = Livewire::actingAs($actor['identity'], 'api')
            ->test(RequestShowPage::class, ['requestId' => $draft->requireId()->value])
            ->call('submit')
            ->assertSet('summary.status', PendingDepartmentManagerState::$name);

        $component->call('submit')
            ->assertSet('actionError', 'Only draft requests can be submitted.');
    });
});

describe('request ui architecture guard', function (): void {
    it('keeps livewire request pages free of persistence orchestration smells', function (): void {
        $forbidden = [
            'DB::transaction',
            'DB::table',
            '->save(',
            'RequestRepositoryContract',
        ];

        foreach (glob(app_path('Modules/Request/Presentation/Livewire/*.php')) ?: [] as $path) {
            $contents = file_get_contents($path);
            expect($contents)->toBeString();

            foreach ($forbidden as $needle) {
                expect($contents)->not->toContain($needle);
            }
        }
    });
});
