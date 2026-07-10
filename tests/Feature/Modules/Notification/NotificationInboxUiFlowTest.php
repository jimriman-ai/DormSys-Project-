<?php

declare(strict_types=1);

use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Modules\Notification\Application\Contracts\EmployeeExistenceReadPort;
use App\Modules\Notification\Application\Contracts\MarkNotificationReadContract;
use App\Modules\Notification\Application\Contracts\NotificationDeliveryContract;
use App\Modules\Notification\Application\Contracts\NotificationInboxReadContract;
use App\Modules\Notification\Application\DTOs\NotificationInboxListQueryDTO;
use App\Modules\Notification\Application\DTOs\NotificationIntentDto;
use App\Modules\Notification\Application\DTOs\NotificationProjectionDto;
use App\Modules\Notification\Application\DTOs\PaginatedNotificationInboxListDTO;
use App\Modules\Notification\Application\Services\NotificationPrincipalEmployeeResolver;
use App\Modules\Notification\Domain\Enums\NotificationType;
use App\Modules\Notification\Infrastructure\Adapters\InMemoryEmployeeExistenceReadAdapter;
use App\Modules\Notification\Presentation\Livewire\NotificationInboxPage;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use App\Support\Exceptions\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Router;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\Support\MockeryTest;

uses(RefreshDatabase::class);

function authenticateNotificationUiUser(UserModel $identity): void
{
    authenticateRequestHttpMutationUser($identity);
    app(MutationPrincipalContextHolder::class)->set($identity->id);
}

function notificationUiActiveEmployees(string ...$employeeIds): void
{
    app()->instance(
        EmployeeExistenceReadPort::class,
        new InMemoryEmployeeExistenceReadAdapter(array_values($employeeIds)),
    );
}

function deliverNotificationUiItem(string $employeeId, string $correlationId, string $title): string
{
    $result = app(NotificationDeliveryContract::class)->deliver(
        NotificationIntentDto::fromArray([
            'correlationId' => $correlationId,
            'notificationType' => NotificationType::RequestApproved->value,
            'recipientEmployeeId' => $employeeId,
            'title' => $title,
            'message' => 'پیام آزمایشی',
            'sourceContext' => 'request',
            'priority' => 'standard',
            'occurredAt' => '2026-07-02T10:30:00Z',
        ]),
    );

    return (string) $result->notificationId;
}

function notificationUiProjectionRow(
    string $title,
    ?string $deepLinkRoute = null,
    ?string $entityId = null,
    bool $isRead = false,
): NotificationProjectionDto {
    return new NotificationProjectionDto(
        id: UuidGenerator::uuid7(),
        notificationType: NotificationType::RequestApproved->value,
        title: $title,
        message: 'پیام آزمایشی',
        entityType: 'request',
        entityId: $entityId,
        deepLinkRoute: $deepLinkRoute,
        isRead: $isRead,
        readAt: $isRead ? new DateTimeImmutable('2026-07-02T11:00:00Z', new DateTimeZone('UTC')) : null,
        createdAt: new DateTimeImmutable('2026-07-02T10:30:00Z', new DateTimeZone('UTC')),
        priority: 'standard',
    );
}

function mockNotificationInboxProjections(string $employeeId, NotificationProjectionDto ...$projections): void
{
    $items = array_values($projections);
    $total = count($items);

    $inbox = MockeryTest::mock(NotificationInboxReadContract::class);
    MockeryTest::expectOnce($inbox, 'listForRecipientPaginated')
        ->with(Mockery::on(static function (mixed $query) use ($employeeId): bool {
            return $query instanceof NotificationInboxListQueryDTO
                && $query->recipientEmployeeId === $employeeId
                && $query->unreadOnly === null
                && $query->page === 1
                && $query->perPage === 50;
        }))
        ->andReturn(new PaginatedNotificationInboxListDTO(
            items: $items,
            total: $total,
            currentPage: 1,
            perPage: 50,
            lastPage: max((int) ceil($total / 50), 1),
        ));

    app()->instance(NotificationInboxReadContract::class, $inbox);
}

/**
 * @return non-empty-string
 */
function notificationLayoutNavHtml(): string
{
    $content = test()->get('/notifications')->assertOk()->getContent();

    if (! is_string($content)) {
        throw new RuntimeException('Expected notification inbox HTML content.');
    }

    $navStart = strpos($content, '<nav class="flex items-center gap-4 text-sm">');

    if ($navStart === false) {
        throw new RuntimeException('Layout nav block not found in notification inbox HTML.');
    }

    $navEnd = strpos($content, '</nav>', $navStart);

    if ($navEnd === false) {
        throw new RuntimeException('Layout nav closing tag not found in notification inbox HTML.');
    }

    $navHtml = substr($content, $navStart, $navEnd - $navStart + strlen('</nav>'));

    if ($navHtml === '') {
        throw new RuntimeException('Extracted layout nav HTML is empty.');
    }

    return $navHtml;
}

/**
 * @return list<string>
 */
function notificationInboxRouteMiddleware(): array
{
    /** @var Router $router */
    $router = app('router');
    $route = $router->getRoutes()->getByName('notifications.index');

    if ($route === null) {
        throw new RuntimeException('Route [notifications.index] is not registered.');
    }

    /** @var list<string> $middleware */
    $middleware = $route->gatherMiddleware();

    return $middleware;
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
    Mockery::close();
});

describe('notification inbox ui access', function (): void {
    it('registers the notifications inbox route', function (): void {
        expect(route('notifications.index'))->toBe(url('/notifications'));
    });

    it('applies the approved authenticated middleware stack', function (): void {
        $required = ['auth:api', 'request.mutation.principal', 'audit.principal'];

        foreach ($required as $entry) {
            expect(notificationInboxRouteMiddleware())->toContain($entry);
        }
    });

    it('redirects guests from the notification inbox', function (): void {
        $this->get('/notifications')->assertRedirect('/login');
    });

    it('renders the authenticated notification inbox page', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateNotificationUiUser($actor['identity']);

        $this->get('/notifications')
            ->assertOk()
            ->assertSee('اعلان‌های من')
            ->assertSee('بروزرسانی');
    });
});

describe('notification inbox ui states', function (): void {
    it('renders an empty inbox state', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateNotificationUiUser($actor['identity']);
        notificationUiActiveEmployees($actor['employee']->requireId()->value);

        Livewire::actingAs($actor['identity'], 'api')
            ->test(NotificationInboxPage::class)
            ->call('refreshList')
            ->assertSet('uiState', 'empty')
            ->assertSet('loadError', null)
            ->assertSee('اعلانی وجود ندارد');
    });

    it('renders populated inbox rows from the read contract', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateNotificationUiUser($actor['identity']);
        $employeeId = $actor['employee']->requireId()->value;
        notificationUiActiveEmployees($employeeId);

        deliverNotificationUiItem($employeeId, 'ui:inbox:001', 'اعلان اول');
        deliverNotificationUiItem($employeeId, 'ui:inbox:002', 'اعلان دوم');

        Livewire::actingAs($actor['identity'], 'api')
            ->test(NotificationInboxPage::class)
            ->call('refreshList')
            ->assertSet('uiState', 'ready')
            ->assertSee('اعلان اول')
            ->assertSee('اعلان دوم')
            ->assertSee('request_approved')
            ->assertSee('standard')
            ->assertSee('خوانده نشده');
    });

    it('resolves employee context through NotificationPrincipalEmployeeResolver', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateNotificationUiUser($actor['identity']);

        expect(app(NotificationPrincipalEmployeeResolver::class)->requireEmployeeId())
            ->toBe($actor['employee']->requireId()->value);
    });

    it('surfaces principal resolution failure as an error state', function (): void {
        $identity = createIdentityUserThroughMutation(
            'Unlinked Notification User',
            'unlinked.notification.'.uniqid('', true).'@example.com',
        );

        $identityModel = UserModel::query()->findOrFail($identity->requireId()->value);
        authenticateNotificationUiUser($identityModel);

        Livewire::actingAs($identityModel, 'api')
            ->test(NotificationInboxPage::class)
            ->call('refreshList')
            ->assertSet('uiState', 'error')
            ->assertSet('loadError', 'Authenticated principal has no linked employee.');
    });

    it('calls listForRecipientPaginated with page 1 and perPage 50', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateNotificationUiUser($actor['identity']);
        $employeeId = $actor['employee']->requireId()->value;

        $inbox = MockeryTest::mock(NotificationInboxReadContract::class);
        MockeryTest::expectOnce($inbox, 'listForRecipientPaginated')
            ->with(Mockery::on(static function (mixed $query) use ($employeeId): bool {
                return $query instanceof NotificationInboxListQueryDTO
                    && $query->recipientEmployeeId === $employeeId
                    && $query->unreadOnly === null
                    && $query->page === 1
                    && $query->perPage === 50;
            }))
            ->andReturn(new PaginatedNotificationInboxListDTO(
                items: [],
                total: 0,
                currentPage: 1,
                perPage: 50,
                lastPage: 1,
            ));

        app()->instance(NotificationInboxReadContract::class, $inbox);

        Livewire::actingAs($actor['identity'], 'api')
            ->test(NotificationInboxPage::class)
            ->call('refreshList')
            ->assertSet('uiState', 'empty')
            ->assertSet('page', 1)
            ->assertSet('total', 0)
            ->assertSet('lastPage', 1);
    });

    it('presents at most 50 inbox items on the default page', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateNotificationUiUser($actor['identity']);
        $employeeId = $actor['employee']->requireId()->value;

        $projections = [];

        for ($index = 1; $index <= 50; $index++) {
            $projections[] = new NotificationProjectionDto(
                id: UuidGenerator::uuid7(),
                notificationType: NotificationType::RequestApproved->value,
                title: 'اعلان '.$index,
                message: 'پیام '.$index,
                entityType: null,
                entityId: null,
                deepLinkRoute: null,
                isRead: false,
                readAt: null,
                createdAt: new DateTimeImmutable('2026-07-02T10:30:00Z', new DateTimeZone('UTC')),
                priority: 'standard',
            );
        }

        $inbox = MockeryTest::mock(NotificationInboxReadContract::class);
        MockeryTest::expectOnce($inbox, 'listForRecipientPaginated')
            ->with(Mockery::on(static function (mixed $query) use ($employeeId): bool {
                return $query instanceof NotificationInboxListQueryDTO
                    && $query->recipientEmployeeId === $employeeId
                    && $query->page === 1
                    && $query->perPage === 50;
            }))
            ->andReturn(new PaginatedNotificationInboxListDTO(
                items: $projections,
                total: 50,
                currentPage: 1,
                perPage: 50,
                lastPage: 1,
            ));

        app()->instance(NotificationInboxReadContract::class, $inbox);

        $component = Livewire::actingAs($actor['identity'], 'api')
            ->test(NotificationInboxPage::class)
            ->call('refreshList')
            ->assertSet('uiState', 'ready')
            ->assertDontSee('قبلی')
            ->assertDontSee('بعدی');

        expect($component->get('notifications'))->toHaveCount(50);
    });

    it('surfaces read contract failures as an error state', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateNotificationUiUser($actor['identity']);

        $inbox = MockeryTest::mock(NotificationInboxReadContract::class);
        MockeryTest::expectOnce($inbox, 'listForRecipientPaginated')
            ->andThrow(new RuntimeException('Inbox read failed.'));

        app()->instance(NotificationInboxReadContract::class, $inbox);

        Livewire::actingAs($actor['identity'], 'api')
            ->test(NotificationInboxPage::class)
            ->call('refreshList')
            ->assertSet('uiState', 'error')
            ->assertSet('loadError', 'Inbox read failed.');
    });
});

describe('notification inbox pagination', function (): void {
    it('navigates to a later page when total exceeds 50', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateNotificationUiUser($actor['identity']);
        $employeeId = $actor['employee']->requireId()->value;
        notificationUiActiveEmployees($employeeId);

        for ($index = 1; $index <= 51; $index++) {
            deliverNotificationUiItem($employeeId, 'ui:page:'.$index, 'اعلان صفحه '.$index);
        }

        Livewire::actingAs($actor['identity'], 'api')
            ->test(NotificationInboxPage::class)
            ->call('refreshList')
            ->assertSet('uiState', 'ready')
            ->assertSet('total', 51)
            ->assertSet('lastPage', 2)
            ->assertSet('page', 1)
            ->assertSee('صفحه 1 از 2')
            ->assertSee('قبلی')
            ->assertSee('بعدی')
            ->call('goToPage', 2)
            ->assertSet('page', 2)
            ->assertSet('lastPage', 2)
            ->assertSee('صفحه 2 از 2');

        $pageTwo = Livewire::actingAs($actor['identity'], 'api')
            ->withQueryParams(['page' => 2])
            ->test(NotificationInboxPage::class)
            ->call('refreshList')
            ->assertSet('page', 2)
            ->assertSet('total', 51);

        expect($pageTwo->get('notifications'))->toHaveCount(1);
    });

    it('keeps the current page after mark-read success', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateNotificationUiUser($actor['identity']);
        $employeeId = $actor['employee']->requireId()->value;
        notificationUiActiveEmployees($employeeId);

        $pageTwoNotificationId = null;

        for ($index = 1; $index <= 51; $index++) {
            $id = deliverNotificationUiItem($employeeId, 'ui:mark-page:'.$index, 'اعلان علامت '.$index);

            if ($index === 1) {
                $pageTwoNotificationId = $id;
            }
        }

        expect($pageTwoNotificationId)->toBeString();

        Livewire::actingAs($actor['identity'], 'api')
            ->withQueryParams(['page' => 2])
            ->test(NotificationInboxPage::class)
            ->call('refreshList')
            ->assertSet('page', 2)
            ->assertSee('اعلان علامت 1')
            ->call('markNotificationRead', $pageTwoNotificationId)
            ->assertSet('actionError', null)
            ->assertSet('page', 2)
            ->assertSee('خوانده شده');
    });
});

describe('notification inbox mark-read mutation', function (): void {
    it('renders the mark-read affordance for unread inbox rows', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateNotificationUiUser($actor['identity']);
        $employeeId = $actor['employee']->requireId()->value;
        notificationUiActiveEmployees($employeeId);

        deliverNotificationUiItem($employeeId, 'ui:mark-read:unread', 'اعلان خوانده نشده');

        Livewire::actingAs($actor['identity'], 'api')
            ->test(NotificationInboxPage::class)
            ->call('refreshList')
            ->assertSet('uiState', 'ready')
            ->assertSee('علامت‌گذاری به‌عنوان خوانده‌شده');
    });

    it('does not render the mark-read affordance for read inbox rows', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateNotificationUiUser($actor['identity']);
        $employeeId = $actor['employee']->requireId()->value;
        notificationUiActiveEmployees($employeeId);

        $notificationId = deliverNotificationUiItem($employeeId, 'ui:mark-read:read', 'اعلان خوانده شده');

        app(MarkNotificationReadContract::class)->markRead(
            $notificationId,
            $employeeId,
            new DateTimeImmutable('2026-07-02T11:00:00Z', new DateTimeZone('UTC')),
        );

        Livewire::actingAs($actor['identity'], 'api')
            ->test(NotificationInboxPage::class)
            ->call('refreshList')
            ->assertSet('uiState', 'ready')
            ->assertSee('خوانده شده')
            ->assertDontSee('علامت‌گذاری به‌عنوان خوانده‌شده');
    });

    it('marks a notification as read through the ui and refreshes rendered state', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateNotificationUiUser($actor['identity']);
        $employeeId = $actor['employee']->requireId()->value;
        notificationUiActiveEmployees($employeeId);

        $notificationId = deliverNotificationUiItem($employeeId, 'ui:mark-read:success', 'اعلان برای علامت‌گذاری');

        Livewire::actingAs($actor['identity'], 'api')
            ->test(NotificationInboxPage::class)
            ->call('refreshList')
            ->assertSet('uiState', 'ready')
            ->assertSee('خوانده نشده')
            ->call('markNotificationRead', $notificationId)
            ->assertSet('actionError', null)
            ->assertSee('خوانده شده')
            ->assertDontSee('خوانده نشده');
    });

    it('delegates mark-read to MarkNotificationReadContract with notification and employee context', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateNotificationUiUser($actor['identity']);
        $employeeId = $actor['employee']->requireId()->value;

        $notificationId = UuidGenerator::uuid7();

        $markRead = MockeryTest::mock(MarkNotificationReadContract::class);
        MockeryTest::expectOnce($markRead, 'markRead')
            ->with($notificationId, $employeeId, Mockery::type(DateTimeImmutable::class));

        $inbox = MockeryTest::mock(NotificationInboxReadContract::class);
        MockeryTest::expectOnce($inbox, 'listForRecipientPaginated')
            ->with(Mockery::on(static function (mixed $query) use ($employeeId): bool {
                return $query instanceof NotificationInboxListQueryDTO
                    && $query->recipientEmployeeId === $employeeId
                    && $query->unreadOnly === null
                    && $query->page === 1
                    && $query->perPage === 50;
            }))
            ->andReturn(new PaginatedNotificationInboxListDTO(
                items: [],
                total: 0,
                currentPage: 1,
                perPage: 50,
                lastPage: 1,
            ));

        app()->instance(MarkNotificationReadContract::class, $markRead);
        app()->instance(NotificationInboxReadContract::class, $inbox);

        Livewire::actingAs($actor['identity'], 'api')
            ->test(NotificationInboxPage::class)
            ->call('markNotificationRead', $notificationId)
            ->assertSet('uiState', 'empty')
            ->assertSet('actionError', null);
    });

    it('surfaces mark-read mutation failures through actionError', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateNotificationUiUser($actor['identity']);

        $notificationId = UuidGenerator::uuid7();

        $markRead = MockeryTest::mock(MarkNotificationReadContract::class);
        MockeryTest::expectOnce($markRead, 'markRead')
            ->andThrow(new ValidationException('Notification not found for recipient.'));

        app()->instance(MarkNotificationReadContract::class, $markRead);

        Livewire::actingAs($actor['identity'], 'api')
            ->test(NotificationInboxPage::class)
            ->call('markNotificationRead', $notificationId)
            ->assertSet('actionError', 'Notification not found for recipient.');
    });
});

describe('notification inbox deep-link navigation', function (): void {
    it('renders request navigation for eligible requests.show rows', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateNotificationUiUser($actor['identity']);
        $employeeId = $actor['employee']->requireId()->value;
        $requestId = UuidGenerator::uuid7();

        mockNotificationInboxProjections(
            $employeeId,
            notificationUiProjectionRow('اعلان قابل مشاهده', 'requests.show', $requestId),
        );

        Livewire::actingAs($actor['identity'], 'api')
            ->test(NotificationInboxPage::class)
            ->call('refreshList')
            ->assertSet('uiState', 'ready')
            ->assertSee('مشاهده')
            ->assertSee(route('requests.show', ['requestId' => $requestId]));
    });

    it('does not render navigation when entityId is null for requests.show rows', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateNotificationUiUser($actor['identity']);
        $employeeId = $actor['employee']->requireId()->value;

        mockNotificationInboxProjections(
            $employeeId,
            notificationUiProjectionRow('اعلان بدون شناسه', 'requests.show', null),
        );

        Livewire::actingAs($actor['identity'], 'api')
            ->test(NotificationInboxPage::class)
            ->call('refreshList')
            ->assertSet('uiState', 'ready')
            ->assertDontSee('مشاهده');
    });

    it('does not render navigation for non-allowlisted deep-link routes', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateNotificationUiUser($actor['identity']);
        $employeeId = $actor['employee']->requireId()->value;
        $allocationId = UuidGenerator::uuid7();

        mockNotificationInboxProjections(
            $employeeId,
            notificationUiProjectionRow('اعلان تخصیص', 'allocations.show', $allocationId),
        );

        Livewire::actingAs($actor['identity'], 'api')
            ->test(NotificationInboxPage::class)
            ->call('refreshList')
            ->assertSet('uiState', 'ready')
            ->assertDontSee('مشاهده');
    });

    it('does not render navigation when deepLinkRoute is null', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateNotificationUiUser($actor['identity']);
        $employeeId = $actor['employee']->requireId()->value;

        mockNotificationInboxProjections(
            $employeeId,
            notificationUiProjectionRow('اعلان بدون مسیر', null, UuidGenerator::uuid7()),
        );

        Livewire::actingAs($actor['identity'], 'api')
            ->test(NotificationInboxPage::class)
            ->call('refreshList')
            ->assertSet('uiState', 'ready')
            ->assertDontSee('مشاهده');
    });

    it('maps request_show_url using requestId binding from entityId', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateNotificationUiUser($actor['identity']);
        $employeeId = $actor['employee']->requireId()->value;
        $requestId = UuidGenerator::uuid7();

        mockNotificationInboxProjections(
            $employeeId,
            notificationUiProjectionRow('اعلان با مسیر', 'requests.show', $requestId),
        );

        $component = Livewire::actingAs($actor['identity'], 'api')
            ->test(NotificationInboxPage::class)
            ->call('refreshList')
            ->assertSet('uiState', 'ready');

        expect($component->get('notifications')[0]['request_show_url'])
            ->toBe(route('requests.show', ['requestId' => $requestId]));
    });

    it('keeps mark-read affordance for unread eligible rows', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateNotificationUiUser($actor['identity']);
        $employeeId = $actor['employee']->requireId()->value;

        mockNotificationInboxProjections(
            $employeeId,
            notificationUiProjectionRow('اعلان خوانده نشده با مسیر', 'requests.show', UuidGenerator::uuid7(), isRead: false),
        );

        Livewire::actingAs($actor['identity'], 'api')
            ->test(NotificationInboxPage::class)
            ->call('refreshList')
            ->assertSet('uiState', 'ready')
            ->assertSee('علامت‌گذاری به‌عنوان خوانده‌شده')
            ->assertSee('مشاهده');
    });
});

describe('notification inbox layout navigation', function (): void {
    it('renders the notification inbox nav link on shared layout pages', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateNotificationUiUser($actor['identity']);

        $this->get('/requests')
            ->assertOk()
            ->assertSee('اعلان‌ها')
            ->assertSee(route('notifications.index'), escape: false);
    });

    it('preserves the existing requests nav item unchanged', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateNotificationUiUser($actor['identity']);

        $this->get('/requests')
            ->assertOk()
            ->assertSee('درخواست‌ها')
            ->assertSee(route('requests.index'), escape: false);
    });

    it('applies active-state styling on the notifications route', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateNotificationUiUser($actor['identity']);

        $navHtml = notificationLayoutNavHtml();

        expect($navHtml)->toContain('اعلان‌ها');
        expect($navHtml)->toContain('font-semibold text-sky-700');
        expect($navHtml)->toContain(route('notifications.index'));
    });

    it('keeps the inbox page title unchanged as observational regression', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateNotificationUiUser($actor['identity']);

        $this->get('/notifications')
            ->assertOk()
            ->assertSee('اعلان‌های من');
    });

    it('renders numeric unread badge beside اعلان‌ها when unread count is greater than zero', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateNotificationUiUser($actor['identity']);
        $employeeId = $actor['employee']->requireId()->value;
        notificationUiActiveEmployees($employeeId);

        deliverNotificationUiItem($employeeId, 'ui:badge:001', 'اعلان نخوانده اول');
        deliverNotificationUiItem($employeeId, 'ui:badge:002', 'اعلان نخوانده دوم');

        $navHtml = notificationLayoutNavHtml();

        expect($navHtml)->toContain('اعلان‌ها');
        expect($navHtml)->toContain('>2<');
        expect($navHtml)->not->toContain('countUnread');
    });

    it('omits unread badge when unread count is zero', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateNotificationUiUser($actor['identity']);
        $employeeId = $actor['employee']->requireId()->value;
        notificationUiActiveEmployees($employeeId);

        $navHtml = notificationLayoutNavHtml();

        expect($navHtml)->toContain('اعلان‌ها');
        expect($navHtml)->not->toContain('rounded-full bg-sky-600');
        expect($navHtml)->not->toMatch('/>\s*0\s*</');
    });

    it('omits unread badge when principal has no linked employee without layout error', function (): void {
        $identity = createIdentityUserThroughMutation(
            'Unlinked Badge User',
            'unlinked.badge.'.uniqid('', true).'@example.com',
        );

        $identityModel = UserModel::query()->findOrFail($identity->requireId()->value);
        authenticateNotificationUiUser($identityModel);

        $navHtml = notificationLayoutNavHtml();

        expect($navHtml)->toContain('اعلان‌ها');
        expect($navHtml)->not->toContain('rounded-full bg-sky-600');
    });

    it('scopes layout unread badge to the authenticated recipient employee', function (): void {
        $actorA = createRequestHttpMutationEmployee();
        $actorB = createRequestHttpMutationEmployee('0000000019');
        notificationUiActiveEmployees(
            $actorA['employee']->requireId()->value,
            $actorB['employee']->requireId()->value,
        );

        deliverNotificationUiItem($actorA['employee']->requireId()->value, 'ui:badge:scope:001', 'اعلان کارمند الف');
        deliverNotificationUiItem($actorA['employee']->requireId()->value, 'ui:badge:scope:002', 'اعلان کارمند الف دوم');

        authenticateNotificationUiUser($actorB['identity']);

        $navHtml = notificationLayoutNavHtml();

        expect($navHtml)->toContain('اعلان‌ها');
        expect($navHtml)->not->toContain('>2<');
        expect($navHtml)->not->toContain('rounded-full bg-sky-600');
    });

    it('uses plain href transport without wire:navigate on layout nav', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateNotificationUiUser($actor['identity']);

        $navHtml = notificationLayoutNavHtml();

        expect($navHtml)->toContain('href="'.route('notifications.index').'"');
        expect($navHtml)->not->toContain('wire:navigate');
    });

    it('renders requests nav before notifications nav in header order', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateNotificationUiUser($actor['identity']);

        $navHtml = notificationLayoutNavHtml();

        $requestsPosition = strpos($navHtml, 'درخواست‌ها');
        $notificationsPosition = strpos($navHtml, 'اعلان‌ها');

        if ($requestsPosition === false || $notificationsPosition === false) {
            throw new RuntimeException('Expected both nav labels in layout nav HTML.');
        }

        expect($requestsPosition)->toBeLessThan($notificationsPosition);
    });
});

describe('notification inbox architecture guard', function (): void {
    it('keeps the livewire inbox page free of persistence orchestration smells', function (): void {
        $path = app_path('Modules/Notification/Presentation/Livewire/NotificationInboxPage.php');
        $contents = file_get_contents($path);

        expect($contents)->toBeString();

        foreach ([
            'DB::transaction',
            'DB::table',
            'NotificationRepositoryContract',
            'NotificationLogModel',
            'countUnread',
        ] as $needle) {
            expect($contents)->not->toContain($needle);
        }
    });

    it('permits governed MarkNotificationReadContract delegation for P5 mark-read', function (): void {
        $path = app_path('Modules/Notification/Presentation/Livewire/NotificationInboxPage.php');
        $contents = file_get_contents($path);

        expect($contents)->toBeString();
        expect($contents)->toContain('MarkNotificationReadContract');
        expect($contents)->toContain('markNotificationRead');
    });

    it('uses frozen requests.show binding for P6 deep-link navigation', function (): void {
        $path = app_path('Modules/Notification/Presentation/Livewire/NotificationInboxPage.php');
        $contents = file_get_contents($path);

        expect($contents)->toBeString();
        expect($contents)->toContain("route(self::APPROVED_REQUEST_SHOW_ROUTE, ['requestId' => \$projection->entityId])");
        expect($contents)->not->toContain('entityType');
        expect($contents)->not->toMatch('/route\s*\(\s*\$projection->deepLinkRoute/');
    });

    it('keeps layout blade free of unread count resolution smells', function (): void {
        $path = resource_path('views/components/layouts/app.blade.php');
        $contents = file_get_contents($path);

        expect($contents)->toBeString();

        foreach ([
            'NotificationRepositoryContract',
            'countUnread',
            'DB::',
            'notification_logs',
        ] as $needle) {
            expect($contents)->not->toContain($needle);
        }
    });
});
