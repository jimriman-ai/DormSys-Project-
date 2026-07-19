<?php

declare(strict_types=1);

use App\Modules\Request\Presentation\Livewire\Stage1ApproverConsolePage;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    Carbon::setTestNow('2026-06-23 12:00:00');
    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);
    app(PermissionRegistrar::class)->forgetCachedPermissions();

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

it('redirects guests from the stage-1 console list', function (): void {
    $this->get('/approvals/stage1')->assertRedirect('/login');
});

it('forbids employee-only identity from the stage-1 console list', function (): void {
    $nonApprover = createNonApproverIdentityForStage1Console();

    $this->actingAs($nonApprover['model'], 'identity')
        ->get('/approvals/stage1')
        ->assertForbidden();
});

it('shows empty-state when dormitory-manager has no pending stage-1 requests', function (): void {
    $approver = createDormitoryManagerApproverForStage1Console();

    Livewire::actingAs($approver['model'], 'identity')
        ->test(Stage1ApproverConsolePage::class)
        ->assertOk()
        ->assertSee('هیچ درخواست در انتظاری وجود ندارد', false)
        ->assertSeeHtml('data-testid="stage1-pending-empty"');
});

it('lists pending stage-1 requests for dormitory-manager', function (): void {
    $submitted = createSubmittedStage1PersonalRequest();
    $approver = createDormitoryManagerApproverForStage1Console();

    Livewire::actingAs($approver['model'], 'identity')
        ->test(Stage1ApproverConsolePage::class)
        ->assertOk()
        ->assertSee((string) $submitted->code, false)
        ->assertSee($submitted->employeeId->value, false)
        ->assertSeeHtml('data-testid="stage1-pending-row"')
        ->assertSeeHtml('data-testid="stage1-pending-count"')
        ->assertDontSee('هیچ درخواست در انتظاری وجود ندارد', false);
});

it('filters the pending list by request code search', function (): void {
    $match = createSubmittedStage1PersonalRequest();
    $other = createSubmittedStage1PersonalRequest();
    $approver = createDormitoryManagerApproverForStage1Console();

    Livewire::actingAs($approver['model'], 'identity')
        ->test(Stage1ApproverConsolePage::class)
        ->set('search', (string) $match->code)
        ->assertSee((string) $match->code, false)
        ->assertDontSee((string) $other->code, false)
        ->assertSeeHtml('data-testid="stage1-search-clear"');
});

it('shows filter empty-state when search matches nothing', function (): void {
    createSubmittedStage1PersonalRequest();
    $approver = createDormitoryManagerApproverForStage1Console();

    Livewire::actingAs($approver['model'], 'identity')
        ->test(Stage1ApproverConsolePage::class)
        ->set('search', 'NO-SUCH-CODE-XYZ')
        ->assertSee('نتیجه‌ای برای این جستجو یافت نشد.', false)
        ->assertSeeHtml('data-testid="stage1-pending-filter-empty"')
        ->assertDontSeeHtml('data-testid="stage1-pending-empty"');
});

it('clears search and restores the full pending list', function (): void {
    $submitted = createSubmittedStage1PersonalRequest();
    $approver = createDormitoryManagerApproverForStage1Console();

    Livewire::actingAs($approver['model'], 'identity')
        ->test(Stage1ApproverConsolePage::class)
        ->set('search', 'NO-SUCH-CODE-XYZ')
        ->call('clearSearch')
        ->assertSet('search', '')
        ->assertSee((string) $submitted->code, false)
        ->assertDontSee('نتیجه‌ای برای این جستجو یافت نشد.', false);
});

it('renders dual-model display label without renaming persisted stage vocabulary', function (): void {
    $approver = createDormitoryManagerApproverForStage1Console();

    Livewire::actingAs($approver['model'], 'identity')
        ->test(Stage1ApproverConsolePage::class)
        ->assertSee('وضعیت صف: در انتظار مدیر واحد', false)
        ->assertSee('نقش دسترسی: مدیر خوابگاه', false)
        ->assertDontSee('ROLE_DEPT_MGR', false);
});
