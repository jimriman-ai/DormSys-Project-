<?php

declare(strict_types=1);

use App\Application\Mutation\Exceptions\UnauthorizedMutationException;
use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\Request\Domain\States\DraftState;
use App\Modules\Request\Presentation\Livewire\RequestListPage;
use App\Modules\Request\Presentation\Livewire\RequestShowPage;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\View\ViewException;
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

function authenticateRequestListDetailNavigationUser(App\Modules\Identity\Infrastructure\Persistence\Models\UserModel $identity): void
{
    authenticateRequestHttpMutationUser($identity);
    app(MutationPrincipalContextHolder::class)->set($identity->id);
}

function uniqueNationalCodeForListDetailNavigationTest(): string
{
    for ($attempt = 0; $attempt < 100; $attempt++) {
        $nine = str_pad((string) random_int(100000000, 999999999), 9, '0', STR_PAD_LEFT);

        for ($check = 0; $check <= 9; $check++) {
            $candidate = $nine.(string) $check;

            if (App\Support\ValueObjects\Identity\NationalCode::isValid($candidate)) {
                return $candidate;
            }
        }
    }

    throw new RuntimeException('Could not generate a valid national code for list detail navigation test.');
}

describe('request list detail navigation ui flow', function (): void {
    it('employee can navigate from owned request list row to existing request show', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateRequestListDetailNavigationUser($actor['identity']);

        $draft = createDraftPersonalRequestForHttp($actor['employee']);
        $requestId = $draft->requireId()->value;

        Livewire::actingAs($actor['identity'], 'api')
            ->test(RequestListPage::class)
            ->call('refreshList')
            ->assertSet('uiState', 'ready')
            ->assertSeeHtml('href="'.route('requests.show', ['requestId' => $requestId]).'"')
            ->assertSee('مشاهده');

        $this->get('/requests/'.$requestId)
            ->assertOk()
            ->assertSee($draft->code->value);

        Livewire::actingAs($actor['identity'], 'api')
            ->test(RequestShowPage::class, ['requestId' => $requestId])
            ->assertSet('summary.request_code', $draft->code->value)
            ->assertSet('summary.request_status', DraftState::$name);
    });

    it('existing ownership authorization remains unchanged for non-owned request detail', function (): void {
        $owner = createRequestHttpMutationEmployee();
        $other = createRequestHttpMutationEmployee(nationalCode: uniqueNationalCodeForListDetailNavigationTest());
        authenticateRequestListDetailNavigationUser($other['identity']);

        $draft = createDraftPersonalRequestForHttp($owner['employee']);

        try {
            Livewire::actingAs($other['identity'], 'api')
                ->test(RequestShowPage::class, ['requestId' => $draft->requireId()->value]);
            $this->fail('Expected unauthorized access to non-owned request detail.');
        } catch (Throwable $exception) {
            $root = $exception instanceof ViewException ? $exception->getPrevious() : $exception;
            expect($root)->toBeInstanceOf(UnauthorizedMutationException::class);
        }
    });

    it('request list remains read-only except for navigation', function (): void {
        $actor = createRequestHttpMutationEmployee();
        authenticateRequestListDetailNavigationUser($actor['identity']);

        $dormitoryId = UuidGenerator::uuid7();

        $draft = app(App\Modules\Request\Application\Services\CreatePersonalRequestAction::class)->execute(
            employeeId: App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId::fromString($actor['employee']->requireId()->value),
            dormitoryId: App\Modules\Request\Domain\ValueObjects\DormitorySiteId::fromString($dormitoryId),
            checkInDate: new DateTimeImmutable('2026-07-01'),
            checkOutDate: new DateTimeImmutable('2026-12-31'),
        );

        $component = Livewire::actingAs($actor['identity'], 'api')
            ->test(RequestListPage::class)
            ->call('refreshList')
            ->assertSet('uiState', 'ready')
            ->assertSee('مشاهده')
            ->assertSeeHtml('href="'.route('requests.show', ['requestId' => $draft->requireId()->value]).'"');

        $html = $component->html();

        expect($html)
            ->not->toContain('wire:click="submit"')
            ->not->toContain('wire:click="cancel"')
            ->not->toContain('wire:click="approve"')
            ->not->toContain('wire:click="reject"')
            ->not->toContain('wire:click="save"');

        $component
            ->assertSee('بروزرسانی')
            ->assertDontSee('ثبت درخواست جدید')
            ->assertDontSee('فیلتر')
            ->assertDontSee('مرتب‌سازی', escape: false);
    });
});
