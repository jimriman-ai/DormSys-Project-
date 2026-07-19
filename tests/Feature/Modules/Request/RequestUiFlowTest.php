<?php

declare(strict_types=1);

use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\Notification\Application\Contracts\EmployeeExistenceReadPort;
use App\Modules\Notification\Application\Contracts\NotificationDeliveryContract;
use App\Modules\Notification\Application\DTOs\NotificationIntentDto;
use App\Modules\Notification\Domain\Enums\NotificationType;
use App\Modules\Notification\Infrastructure\Adapters\InMemoryEmployeeExistenceReadAdapter;
use App\Modules\Request\Presentation\Livewire\PersonalRequestFormPage;
use App\Modules\Request\Presentation\Livewire\RequestListPage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

require_once __DIR__.'/support/http-mutation.php';

function authenticateRequestUiUser(App\Modules\Identity\Infrastructure\Persistence\Models\UserModel $identity): void
{
    authenticateRequestHttpMutationUser($identity);
    Auth::guard('identity')->login($identity);
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
            ->assertSee('بروزرسانی')
            ->assertSee('ثبت درخواست جدید')
            ->assertSee(route('requests.create'), escape: false)
            ->assertSee('اعلان‌ها')
            ->assertSee(route('notifications.index'), escape: false)
            ->assertDontSee('مشاهده');
    });

    it('renders layout unread badge on requests page when unread notifications exist', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateRequestUiUser($actor['identity']);
        $employeeId = $actor['employee']->requireId()->value;

        app()->instance(
            EmployeeExistenceReadPort::class,
            new InMemoryEmployeeExistenceReadAdapter([$employeeId]),
        );

        app(NotificationDeliveryContract::class)->deliver(
            NotificationIntentDto::fromArray([
                'correlationId' => 'request-ui:badge:001',
                'notificationType' => NotificationType::RequestApproved->value,
                'recipientEmployeeId' => $employeeId,
                'title' => 'اعلان برای نشان',
                'message' => 'پیام آزمایشی',
                'sourceContext' => 'request',
                'priority' => 'standard',
                'occurredAt' => '2026-07-02T10:30:00Z',
            ]),
        );

        $this->get('/requests')
            ->assertOk()
            ->assertSee('>1<', escape: false);
    });

    it('renders contract-aligned list states through livewire', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateRequestUiUser($actor['identity']);

        Livewire::actingAs($actor['identity'], 'api')
            ->test(RequestListPage::class)
            ->call('refreshList')
            ->assertSet('uiState', 'empty')
            ->assertSet('loadError', null)
            ->assertSee('ثبت درخواست جدید')
            ->assertSeeHtml('href="'.route('requests.create').'"');
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

        $dormitoryId = createDormitorySiteForRequestTests();

        Livewire::actingAs($actor['identity'], 'api')
            ->test(PersonalRequestFormPage::class)
            ->set('dormitory_site_id', $dormitoryId)
            ->set('check_in_date', '2026-07-01')
            ->set('check_out_date', '2026-12-31')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect(route('employee.requests.index'));

        $this->get('/requests')
            ->assertOk();

        Livewire::actingAs($actor['identity'], 'api')
            ->test(RequestListPage::class)
            ->call('refreshList')
            ->assertSet('uiState', 'ready')
            ->assertSee((string) $dormitoryId);
    });

    it('shows backend validation feedback on create', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateRequestUiUser($actor['identity']);

        Livewire::actingAs($actor['identity'], 'api')
            ->test(PersonalRequestFormPage::class)
            ->set('dormitory_site_id', 'not-a-uuid')
            ->set('check_in_date', '2026-07-01')
            ->set('check_out_date', '2026-12-31')
            ->call('submit')
            ->assertHasErrors(['dormitory_site_id']);
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
