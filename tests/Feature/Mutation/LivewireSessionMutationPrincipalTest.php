<?php

declare(strict_types=1);

use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\Audit\Presentation\Http\Middleware\ResolveAuditPrincipalMiddleware;
use App\Modules\Employee\Application\Contracts\EmployeeRepositoryContract;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Modules\Request\Infrastructure\Persistence\Models\RequestModel;
use App\Modules\Request\Presentation\Http\Middleware\EnforceSessionMutationPrincipalMiddleware;
use Database\Seeders\DevelopmentUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Livewire\Drawer\Utils;
use Livewire\Livewire;

uses(RefreshDatabase::class);

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

it('registers session mutation principal middleware as livewire persistent middleware', function (): void {
    expect(Livewire::getPersistentMiddleware())->toContain(
        EnforceSessionMutationPrincipalMiddleware::class,
        ResolveAuditPrincipalMiddleware::class,
    );
});

it('creates a personal request through the create page livewire flow after session login', function (): void {
    Artisan::call('db:seed', ['--class' => DevelopmentUserSeeder::class]);

    $this->post('/login', [
        'identifier' => DevelopmentUserSeeder::EMPLOYEE_EMAIL,
        'password' => DevelopmentUserSeeder::EMPLOYEE_PASSWORD,
    ])->assertRedirect(route('requests.index'));

    $identity = UserModel::query()
        ->where('email', DevelopmentUserSeeder::EMPLOYEE_EMAIL)
        ->firstOrFail();
    grantDormitoryStructureViewPermission($identity->id);

    app(MutationPrincipalContextHolder::class)->clear();
    request()->attributes->remove('audit_principal_user_id');

    $createPage = $this->get('/requests/create');
    $createPage->assertOk();

    $snapshot = Utils::extractAttributeDataFromHtml($createPage->getContent(), 'wire:snapshot');
    expect($snapshot)->toBeArray()
        ->and($snapshot['memo']['path'] ?? null)->toBe('requests/create');

    $dormitoryId = createDormitorySiteForRequestTests();

    $updateResponse = $this->post('/livewire/update', [
        'components' => [
            [
                'snapshot' => json_encode($snapshot, JSON_THROW_ON_ERROR),
                'calls' => [
                    [
                        'method' => 'submit',
                        'params' => [],
                        'path' => '',
                    ],
                ],
                'updates' => [
                    'dormitory_site_id' => $dormitoryId,
                    'check_in_date' => '2026-07-01',
                    'check_out_date' => '2026-12-31',
                ],
            ],
        ],
    ], [
        'X-Livewire' => true,
    ]);

    $updateResponse->assertOk();

    $effects = $updateResponse->json('components.0.effects');
    expect($effects)->toBeArray()
        ->and($effects['redirect'] ?? null)->toBeString()
        ->and($effects['redirect'])->toContain('/employee/requests');
});

it('does not resolve a mutation principal for guests on request create livewire actions', function (): void {
    Artisan::call('db:seed', ['--class' => DevelopmentUserSeeder::class]);

    $createPage = $this->get('/requests/create');
    $createPage->assertRedirect('/login');
});

it('persists a request owned by the authenticated identity employee after livewire submit', function (): void {
    Artisan::call('db:seed', ['--class' => DevelopmentUserSeeder::class]);

    $this->post('/login', [
        'identifier' => DevelopmentUserSeeder::EMPLOYEE_EMAIL,
        'password' => DevelopmentUserSeeder::EMPLOYEE_PASSWORD,
    ])->assertRedirect(route('requests.index'));

    app(MutationPrincipalContextHolder::class)->clear();
    request()->attributes->remove('audit_principal_user_id');

    $identity = UserModel::query()
        ->where('email', DevelopmentUserSeeder::EMPLOYEE_EMAIL)
        ->firstOrFail();

    grantDormitoryStructureViewPermission($identity->id);

    $employeeId = app(EmployeeRepositoryContract::class)->findEmployeeIdByIdentityUserId($identity->id);
    expect($employeeId)->toBeString();

    $createPage = $this->get('/requests/create');
    $snapshot = Utils::extractAttributeDataFromHtml($createPage->getContent(), 'wire:snapshot');

    $dormitoryId = createDormitorySiteForRequestTests();

    $this->post('/livewire/update', [
        'components' => [
            [
                'snapshot' => json_encode($snapshot, JSON_THROW_ON_ERROR),
                'calls' => [
                    [
                        'method' => 'submit',
                        'params' => [],
                        'path' => '',
                    ],
                ],
                'updates' => [
                    'dormitory_site_id' => $dormitoryId,
                    'check_in_date' => '2026-07-01',
                    'check_out_date' => '2026-12-31',
                ],
            ],
        ],
    ], [
        'X-Livewire' => true,
    ])->assertOk();

    expect(RequestModel::query()->where('employee_id', $employeeId)->count())->toBe(1);
});
