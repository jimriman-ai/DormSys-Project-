<?php

declare(strict_types=1);

use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Modules\Notification\Application\Contracts\EmployeeExistenceReadPort;
use App\Modules\Notification\Application\Contracts\MarkNotificationReadContract;
use App\Modules\Notification\Application\Contracts\NotificationDeliveryContract;
use App\Modules\Notification\Application\Contracts\NotificationInboxReadContract;
use App\Modules\Notification\Application\DTOs\NotificationIntentDto;
use App\Modules\Notification\Application\DTOs\NotificationProjectionDto;
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
use Mockery as MockeryTest;

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
    MockeryTest::close();
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

    it('calls listForRecipient with the locked limit of 50', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateNotificationUiUser($actor['identity']);
        $employeeId = $actor['employee']->requireId()->value;

        $inbox = MockeryTest::mock(NotificationInboxReadContract::class);
        $inbox->shouldReceive('listForRecipient')
            ->once()
            ->with($employeeId, null, 50)
            ->andReturn([]);

        app()->instance(NotificationInboxReadContract::class, $inbox);

        Livewire::actingAs($actor['identity'], 'api')
            ->test(NotificationInboxPage::class)
            ->call('refreshList')
            ->assertSet('uiState', 'empty');
    });

    it('presents at most 50 inbox items', function (): void {
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
        $inbox->shouldReceive('listForRecipient')
            ->once()
            ->with($employeeId, null, 50)
            ->andReturn($projections);

        app()->instance(NotificationInboxReadContract::class, $inbox);

        $component = Livewire::actingAs($actor['identity'], 'api')
            ->test(NotificationInboxPage::class)
            ->call('refreshList')
            ->assertSet('uiState', 'ready');

        expect($component->get('notifications'))->toHaveCount(50);
    });

    it('surfaces read contract failures as an error state', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateNotificationUiUser($actor['identity']);

        $inbox = MockeryTest::mock(NotificationInboxReadContract::class);
        $inbox->shouldReceive('listForRecipient')
            ->once()
            ->andThrow(new RuntimeException('Inbox read failed.'));

        app()->instance(NotificationInboxReadContract::class, $inbox);

        Livewire::actingAs($actor['identity'], 'api')
            ->test(NotificationInboxPage::class)
            ->call('refreshList')
            ->assertSet('uiState', 'error')
            ->assertSet('loadError', 'Inbox read failed.');
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
        $markRead->shouldReceive('markRead')
            ->once()
            ->with($notificationId, $employeeId, MockeryTest::type(DateTimeImmutable::class));

        $inbox = MockeryTest::mock(NotificationInboxReadContract::class);
        $inbox->shouldReceive('listForRecipient')
            ->once()
            ->with($employeeId, null, 50)
            ->andReturn([]);

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
        $markRead->shouldReceive('markRead')
            ->once()
            ->andThrow(new ValidationException('Notification not found for recipient.'));

        app()->instance(MarkNotificationReadContract::class, $markRead);

        Livewire::actingAs($actor['identity'], 'api')
            ->test(NotificationInboxPage::class)
            ->call('markNotificationRead', $notificationId)
            ->assertSet('actionError', 'Notification not found for recipient.');
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
});
