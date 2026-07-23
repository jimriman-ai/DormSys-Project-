<?php

declare(strict_types=1);

use App\Application\Mutation\Contracts\MutationPrincipalContextPort;
use App\Application\Mutation\Exceptions\UnauthorizedMutationException;
use App\Application\Mutation\Support\MutationPrincipalContext;
use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\Allocation\Application\Contracts\AllocationRepositoryContract;
use App\Modules\Allocation\Application\Services\ProposedAllocationConsumer;
use App\Modules\Allocation\Domain\ValueObjects\PersonAllocationRef;
use App\Modules\Identity\Application\Services\CreateUserAction;
use App\Modules\Identity\Presentation\Console\CreateUserCommand;
use App\Modules\Lottery\Infrastructure\Jobs\AutoLockLotteryJob;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use App\Shared\ValueObjects\SystemActorId;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(MutationPrincipalContextHolder::class)->clear();
    request()->attributes->remove('audit_principal_user_id');
});

it('scopes runAs to holder only and suppresses ambient request principal during execution', function (): void {
    $ambientPrincipal = UuidGenerator::uuid7();
    $scopedPrincipal = SystemActorId::VALUE;

    request()->attributes->set('audit_principal_user_id', $ambientPrincipal);

    MutationPrincipalContext::runAs($scopedPrincipal, function () use ($scopedPrincipal, $ambientPrincipal): void {
        expect(app(MutationPrincipalContextPort::class)->currentPrincipalId())->toBe($scopedPrincipal)
            ->and(request()->attributes->get('audit_principal_user_id'))->toBe($scopedPrincipal)
            ->and(request()->attributes->get('audit_principal_user_id'))->not->toBe($ambientPrincipal);
    });

    expect(app(MutationPrincipalContextHolder::class)->get())->toBeNull()
        ->and(request()->attributes->get('audit_principal_user_id'))->toBe($ambientPrincipal);
});

it('does not restore a leaked holder principal after runJobAsSystem completes', function (): void {
    app(MutationPrincipalContextHolder::class)->set(UuidGenerator::uuid7());

    MutationPrincipalContext::runJobAsSystem(static fn (): null => null);

    expect(app(MutationPrincipalContextHolder::class)->get())->toBeNull();
});

it('fails closed when proposed allocation consumer is invoked without caller principal', function (): void {
    expect(fn () => app(ProposedAllocationConsumer::class)->emitProposedAllocations([
        [
            'program_id' => UuidGenerator::uuid7(),
            'lottery_result_id' => UuidGenerator::uuid7(),
            'registration_id' => UuidGenerator::uuid7(),
            'employee_id' => UuidGenerator::uuid7(),
            'dormitory_id' => UuidGenerator::uuid7(),
            'rank' => 1,
        ],
    ]))->toThrow(UnauthorizedMutationException::class);
});

it('delegates lottery runtime allocation creation through inherited system principal without alternate authority', function (): void {
    $personId = UuidGenerator::uuid7();
    $dormitoryId = createAssignableBedForAllocationTests();

    MutationPrincipalContext::runAsSystem(function () use ($personId, $dormitoryId): void {
        app(ProposedAllocationConsumer::class)->emitProposedAllocations([
            [
                'program_id' => UuidGenerator::uuid7(),
                'lottery_result_id' => UuidGenerator::uuid7(),
                'registration_id' => UuidGenerator::uuid7(),
                'employee_id' => $personId,
                'dormitory_id' => $dormitoryId,
                'rank' => 1,
            ],
        ]);
    });

    expect(app(AllocationRepositoryContract::class)->findActiveByPersonId(PersonAllocationRef::fromString($personId)))->not->toBeEmpty();
});

it('does not leave system actor principal after auto lock job execution', function (): void {
    app()->call([app(AutoLockLotteryJob::class), 'handle']);

    expect(app(MutationPrincipalContextHolder::class)->get())->toBeNull();
});

it('fails closed for adopted identity CLI mutation without principal', function (): void {
    expect(fn () => app(CreateUserAction::class)->execute('CLI Principal Containment', 'cli@example.com'))
        ->toThrow(UnauthorizedMutationException::class);

    expect(fn () => $this->artisan(CreateUserCommand::class, [
        'display_name' => 'CLI Principal Containment',
        '--email' => 'cli-command@example.com',
    ]))->toThrow(UnauthorizedMutationException::class);
});
