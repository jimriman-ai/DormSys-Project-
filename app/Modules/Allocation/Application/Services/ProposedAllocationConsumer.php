<?php

declare(strict_types=1);

namespace App\Modules\Allocation\Application\Services;

use App\Modules\Allocation\Domain\Enums\AllocationMethod;
use App\Modules\Lottery\Application\Contracts\ProposedAllocationPort;
use App\Support\Exceptions\ValidationException;
use DateTimeImmutable;
use DateTimeZone;

/**
 * Consumes lottery draw winners and delegates allocation creation.
 *
 * Intake is push-only from locked lottery snapshot selections via
 * {@see \App\Modules\Lottery\Application\Services\ExecuteDrawAction}; this consumer must not read live Request state
 * or lottery result tables for eligibility meaning.
 *
 * Propagation: inherits the caller-established mutation principal from
 * {@see \App\Application\Mutation\Support\MutationPrincipalContextHolder}; does not establish authority itself.
 * Authoritative enforcement remains at {@see CreateAllocationAction} via MPEP.
 */
final class ProposedAllocationConsumer implements ProposedAllocationPort
{
    public function __construct(
        private readonly CreateAllocationAction $createAllocation,
    ) {}

    /**
     * @param  list<array<string, mixed>>  $winners
     */
    public function emitProposedAllocations(array $winners): void
    {
        foreach ($winners as $winner) {
            $this->assertFrozenLotteryWinnerPayload($winner);

            $this->createAllocation->execute(
                personId: (string) $winner['employee_id'],
                bedId: (string) $winner['dormitory_id'],
                start: new DateTimeImmutable('now', new DateTimeZone('UTC')),
                end: new DateTimeImmutable('+1 year', new DateTimeZone('UTC')),
                method: AllocationMethod::LotterySourced,
                sourceLotteryResultId: (string) $winner['lottery_result_id'],
            );
        }
    }

    /**
     * @param  array<string, mixed>  $winner
     */
    private function assertFrozenLotteryWinnerPayload(array $winner): void
    {
        foreach (['program_id', 'lottery_result_id', 'registration_id', 'employee_id', 'dormitory_id', 'rank'] as $key) {
            if (! array_key_exists($key, $winner) || $winner[$key] === '' || $winner[$key] === null) {
                throw new ValidationException("Lottery winner payload missing required frozen field [{$key}].");
            }
        }
    }
}
