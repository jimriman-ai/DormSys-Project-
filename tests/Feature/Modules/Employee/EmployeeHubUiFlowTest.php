<?php

declare(strict_types=1);

use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\Employee\Presentation\Livewire\EmployeeHubPage;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Support\ValueObjects\Identity\NationalCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function authenticateEmployeeHubUiUser(UserModel $identity): void
{
    authenticateRequestHttpMutationUser($identity);
    app(MutationPrincipalContextHolder::class)->set($identity->id);
}

function uniqueNationalCodeForEmployeeHubUi(): string
{
    for ($attempt = 0; $attempt < 100; $attempt++) {
        $nine = str_pad((string) random_int(100000000, 999999999), 9, '0', STR_PAD_LEFT);

        for ($check = 0; $check <= 9; $check++) {
            $candidate = $nine.(string) $check;

            if (NationalCode::isValid($candidate)) {
                return $candidate;
            }
        }
    }

    throw new RuntimeException('Could not generate a valid national code for employee hub UI test.');
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

describe('employee hub ui access', function (): void {
    it('redirects guests from the employee hub', function (): void {
        $this->get('/employees')->assertRedirect('/login');
    });

    it('renders the authenticated employee hub page', function (): void {
        $actor = createRequestHttpMutationEmployee(uniqueNationalCodeForEmployeeHubUi());
        authenticateEmployeeHubUiUser($actor['identity']);

        $this->get('/employees')
            ->assertOk()
            ->assertSee('کارکنان')
            ->assertSee('ایجاد کارمند')
            ->assertSee('ایجاد دپارتمان')
            ->assertSee('تخصیص دپارتمان به کارمند')
            ->assertSee('غیرفعال‌سازی دپارتمان')
            ->assertSee(route('employees.hub'), escape: false)
            ->assertDontSee('لیست کارمندان')
            ->assertDontSee('جستجوی کارمند');
    });

    it('places کارکنان nav immediately after اعلان‌ها', function (): void {
        $actor = createRequestHttpMutationEmployee(uniqueNationalCodeForEmployeeHubUi());
        authenticateEmployeeHubUiUser($actor['identity']);

        $html = $this->get('/employees')->assertOk()->getContent();

        if (! is_string($html)) {
            throw new RuntimeException('Expected employee hub HTML content.');
        }

        expect($html)->toContain('href="'.route('requests.index').'"')
            ->and($html)->toContain('href="'.route('notifications.index').'"')
            ->and($html)->toContain('href="'.route('employees.hub').'"')
            ->and($html)->toMatch('/درخواست‌ها[\s\S]*اعلان‌ها[\s\S]*کارکنان/u');

        $navStart = strpos($html, '<nav');
        $navEnd = strpos($html, '</nav>');
        expect($navStart)->not->toBeFalse()->and($navEnd)->not->toBeFalse();

        if ($navStart === false || $navEnd === false) {
            throw new RuntimeException('Layout nav block not found in employee hub HTML.');
        }

        $navHtml = substr($html, $navStart, $navEnd - $navStart);
        expect($navHtml)->not->toContain('wire:navigate');
    });
});

describe('employee hub mutations', function (): void {
    it('creates an employee via CreateEmployeeAction', function (): void {
        $actor = createRequestHttpMutationEmployee(uniqueNationalCodeForEmployeeHubUi());
        authenticateEmployeeHubUiUser($actor['identity']);

        $targetIdentity = createIdentityUserThroughMutation(
            'Hub Target',
            'hub.target.'.uniqid('', true).'@example.com',
        );

        Livewire::actingAs($actor['identity'], 'api')
            ->test(EmployeeHubPage::class)
            ->set('identityId', $targetIdentity->requireId()->value)
            ->set('employeeCode', 'EMP-HUB-001')
            ->set('firstName', 'علی')
            ->set('lastName', 'تست')
            ->set('nationalCode', uniqueNationalCodeForEmployeeHubUi())
            ->set('hireDate', '2024-01-15')
            ->call('createEmployee')
            ->assertHasNoErrors()
            ->assertSet('actionError', null)
            ->assertSet('successMessage', 'کارمند با موفقیت ایجاد شد.')
            ->assertNotSet('returnedId', null);
    });

    it('creates a department via CreateDepartmentAction', function (): void {
        $actor = createRequestHttpMutationEmployee(uniqueNationalCodeForEmployeeHubUi());
        authenticateEmployeeHubUiUser($actor['identity']);

        Livewire::actingAs($actor['identity'], 'api')
            ->test(EmployeeHubPage::class)
            ->set('departmentName', 'دپارتمان تست')
            ->set('departmentCode', 'DEPT-HUB-'.substr(uniqid('', true), -6))
            ->set('lotteryPriority', '0')
            ->call('createDepartment')
            ->assertHasNoErrors()
            ->assertSet('actionError', null)
            ->assertSet('successMessage', 'دپارتمان با موفقیت ایجاد شد.')
            ->assertNotSet('returnedId', null);
    });

    it('assigns a department via AssignDepartmentToEmployeeAction', function (): void {
        $actor = createRequestHttpMutationEmployee(uniqueNationalCodeForEmployeeHubUi());
        authenticateEmployeeHubUiUser($actor['identity']);

        $department = createDepartmentThroughMutation(
            name: 'Assign Hub Dept',
            code: 'DEPT-ASN-'.substr(uniqid('', true), -6),
            actorId: $actor['identity']->id,
        );

        Livewire::actingAs($actor['identity'], 'api')
            ->test(EmployeeHubPage::class)
            ->set('assignEmployeeId', $actor['employee']->requireId()->value)
            ->set('assignDepartmentId', $department->requireId()->value)
            ->call('assignDepartment')
            ->assertHasNoErrors()
            ->assertSet('actionError', null)
            ->assertSet('successMessage', 'دپارتمان به کارمند تخصیص داده شد.')
            ->assertSet('returnedId', $actor['employee']->requireId()->value);
    });

    it('deactivates a department via DeactivateDepartmentAction', function (): void {
        $actor = createRequestHttpMutationEmployee(uniqueNationalCodeForEmployeeHubUi());
        authenticateEmployeeHubUiUser($actor['identity']);

        $department = createDepartmentThroughMutation(
            name: 'Deactivate Hub Dept',
            code: 'DEPT-DEA-'.substr(uniqid('', true), -6),
            actorId: $actor['identity']->id,
        );

        Livewire::actingAs($actor['identity'], 'api')
            ->test(EmployeeHubPage::class)
            ->set('deactivateDepartmentId', $department->requireId()->value)
            ->call('deactivateDepartment')
            ->assertHasNoErrors()
            ->assertSet('actionError', null)
            ->assertSet('successMessage', 'دپارتمان غیرفعال شد.')
            ->assertSet('returnedId', $department->requireId()->value);
    });

    it('surfaces backend failure without remapping when identity is unknown', function (): void {
        $actor = createRequestHttpMutationEmployee(uniqueNationalCodeForEmployeeHubUi());
        authenticateEmployeeHubUiUser($actor['identity']);

        Livewire::actingAs($actor['identity'], 'api')
            ->test(EmployeeHubPage::class)
            ->set('identityId', '11111111-1111-1111-1111-111111111111')
            ->set('employeeCode', 'EMP-HUB-FAIL')
            ->set('firstName', 'Fail')
            ->set('lastName', 'Case')
            ->set('nationalCode', uniqueNationalCodeForEmployeeHubUi())
            ->set('hireDate', '2024-01-15')
            ->call('createEmployee')
            ->assertSet('actionError', 'Identity user does not exist.')
            ->assertSet('returnedId', null);
    });
});

describe('employee hub architecture guard', function (): void {
    it('keeps EmployeeHubPage free of repository, eloquent, and db access', function (): void {
        $path = app_path('Modules/Employee/Presentation/Livewire/EmployeeHubPage.php');
        $contents = file_get_contents($path);

        expect($contents)->not->toBeFalse();

        foreach ([
            'EmployeeRepositoryContract',
            'DepartmentRepositoryContract',
            'DB::',
            'Eloquent',
            '::query(',
            'hasRole',
            'Gate::',
        ] as $forbidden) {
            expect($contents)->not->toContain($forbidden);
        }

        expect($contents)->toContain('CreateEmployeeAction')
            ->and($contents)->toContain('CreateDepartmentAction')
            ->and($contents)->toContain('AssignDepartmentToEmployeeAction')
            ->and($contents)->toContain('DeactivateDepartmentAction');
    });

    it('does not introduce excluded list or selector UX in the hub blade', function (): void {
        $path = resource_path('views/livewire/employee/employee-hub-page.blade.php');
        $contents = file_get_contents($path);

        expect($contents)->not->toBeFalse()
            ->and($contents)->not->toContain('<select')
            ->and($contents)->not->toContain('typeahead')
            ->and($contents)->not->toContain('autocomplete')
            ->and($contents)->not->toContain('لیست کارمندان')
            ->and($contents)->not->toContain('درخت دپارتمان');
    });
});
