<?php

declare(strict_types=1);

use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\Audit\Application\Contracts\AuditRecordingContract;
use App\Modules\Audit\Application\DTOs\AuditEntryDto;
use App\Modules\Audit\Domain\Enums\ActorType;
use App\Modules\Audit\Domain\Enums\AuditEventType;
use App\Modules\Audit\Presentation\Livewire\AuditHistoryPage;
use App\Modules\Identity\Domain\ValueObjects\UserId;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function authenticateAuditUiUser(UserModel $identity): void
{
    authenticateRequestHttpMutationUser($identity);
    app(MutationPrincipalContextHolder::class)->set($identity->id);
}

/**
 * @return array{employee: \App\Modules\Employee\Domain\Entities\Employee, identity: UserModel, email: string, password: string}
 */
function createAuditUiReader(): array
{
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);

    $actor = createRequestHttpMutationEmployee(
        nationalCode: '0499370899',
        email: 'audit.ui.reader.'.uniqid('', true).'@example.com',
    );

    assignRoleThroughMutation(
        UserId::fromString($actor['identity']->id),
        IdentityRoleSeeder::ROLE_ADMINISTRATOR,
    );

    return $actor;
}

/**
 * @return array{employee: \App\Modules\Employee\Domain\Entities\Employee, identity: UserModel, email: string, password: string}
 */
function createAuditUiUnauthorizedUser(): array
{
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);

    return createRequestHttpMutationEmployee(
        nationalCode: '0013542419',
        email: 'audit.ui.denied.'.uniqid('', true).'@example.com',
    );
}

function seedAuditUiHistoryEntry(string $correlationId): void
{
    app(AuditRecordingContract::class)->record(AuditEntryDto::fromArray([
        'correlationId' => $correlationId,
        'eventType' => AuditEventType::RequestApproved->value,
        'entityType' => 'request',
        'entityId' => UuidGenerator::uuid7(),
        'actorType' => ActorType::User->value,
        'actorId' => UuidGenerator::uuid7(),
        'sourceContext' => 'request',
        'oldValues' => ['status' => 'pending'],
        'newValues' => ['status' => 'approved'],
        'occurredAt' => '2026-07-02T10:00:00Z',
    ]));
}

beforeEach(function (): void {
    Carbon::setTestNow('2026-07-11 12:00:00');
    config(['audit.sync_in_tests' => true]);
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

describe('audit ui access', function (): void {
    it('redirects guests from the audit history page', function (): void {
        $this->get('/audit')->assertRedirect('/login');
    });

    it('forbids authenticated users without audit.read', function (): void {
        $actor = createAuditUiUnauthorizedUser();
        authenticateAuditUiUser($actor['identity']);

        $this->get('/audit')->assertForbidden();
    });

    it('renders the audit history page for audit.read holders', function (): void {
        $actor = createAuditUiReader();
        authenticateAuditUiUser($actor['identity']);
        seedAuditUiHistoryEntry('audit:ui:visible:001');

        $this->get('/audit')
            ->assertOk()
            ->assertSee('تاریخچه حسابرسی')
            ->assertSee(route('audit.index'), escape: false)
            ->assertDontSee('type="search"', false)
            ->assertDontSee('wire:model');
    });

    it('shows role-scoped audit nav only for audit.read holders', function (): void {
        $reader = createAuditUiReader();
        authenticateAuditUiUser($reader['identity']);

        $readerHtml = $this->get('/requests')->assertOk()->getContent();
        expect($readerHtml)->toContain('تاریخچه حسابرسی')
            ->and($readerHtml)->toContain(route('audit.index'));

        $denied = createAuditUiUnauthorizedUser();
        authenticateAuditUiUser($denied['identity']);

        $deniedHtml = $this->get('/requests')->assertOk()->getContent();
        expect($deniedHtml)->not->toContain('تاریخچه حسابرسی')
            ->and($deniedHtml)->not->toContain(route('audit.index'));
    });
});

describe('audit ui read surface', function (): void {
    it('loads history rows through AuditHistoryReadContract without write actions', function (): void {
        $actor = createAuditUiReader();
        authenticateAuditUiUser($actor['identity']);
        seedAuditUiHistoryEntry('audit:ui:row:001');

        Livewire::actingAs($actor['identity'], 'api')
            ->test(AuditHistoryPage::class)
            ->call('refreshList')
            ->assertSet('uiState', 'ready')
            ->assertSee('request.approved')
            ->assertSee('audit:ui:row:001')
            ->assertDontSee('حذف')
            ->assertDontSee('ویرایش')
            ->assertDontSee('خروجی');
    });

    it('shows empty state when the read contract returns no items', function (): void {
        $actor = createAuditUiReader();
        authenticateAuditUiUser($actor['identity']);

        $historyRead = Mockery::mock(App\Modules\Audit\Application\Contracts\AuditHistoryReadContract::class);
        $historyRead->shouldReceive('query')
            ->once()
            ->andReturn(new App\Modules\Audit\Application\DTOs\PaginatedAuditHistoryDto(
                items: [],
                total: 0,
                page: 1,
                perPage: 50,
                lastPage: 1,
            ));
        app()->instance(App\Modules\Audit\Application\Contracts\AuditHistoryReadContract::class, $historyRead);

        Livewire::actingAs($actor['identity'], 'api')
            ->test(AuditHistoryPage::class)
            ->call('refreshList')
            ->assertSet('uiState', 'empty')
            ->assertSet('total', 0)
            ->assertSee('رکوردی وجود ندارد');
    });
});

describe('audit ui architecture guard', function (): void {
    it('keeps AuditHistoryPage free of persistence and permission-derivation smells', function (): void {
        $path = app_path('Modules/Audit/Presentation/Livewire/AuditHistoryPage.php');
        $contents = file_get_contents($path);

        expect($contents)->not->toBeFalse();

        foreach ([
            'DB::',
            'Eloquent',
            '::query(',
            'hasRole',
            'Gate::',
            'AuditLogModel',
            'AuditLogRepository',
        ] as $forbidden) {
            expect($contents)->not->toContain($forbidden);
        }

        expect($contents)->toContain('AuditHistoryReadContract')
            ->and($contents)->toContain('AuditEventTypeCatalogPort');
    });

    it('does not introduce filter or search controls in the audit blade', function (): void {
        $path = resource_path('views/livewire/audit/audit-history-page.blade.php');
        $contents = file_get_contents($path);

        expect($contents)->not->toBeFalse()
            ->and($contents)->not->toContain('<select')
            ->and($contents)->not->toContain('type="search"')
            ->and($contents)->not->toContain('wire:model')
            ->and($contents)->not->toContain('جستجو')
            ->and($contents)->not->toContain('فیلتر');
    });
});
