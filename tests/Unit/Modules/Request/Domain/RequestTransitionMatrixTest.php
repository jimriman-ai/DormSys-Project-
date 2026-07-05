<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Request\Domain;

use App\Modules\Request\Domain\Entities\Request;
use App\Modules\Request\Domain\Enums\RequestType;
use App\Modules\Request\Domain\States\ApprovedState;
use App\Modules\Request\Domain\States\CancelledState;
use App\Modules\Request\Domain\States\DraftState;
use App\Modules\Request\Domain\States\PendingDepartmentManagerState;
use App\Modules\Request\Domain\States\PendingDormitoryManagerState;
use App\Modules\Request\Domain\States\PendingDormitoryUnitState;
use App\Modules\Request\Domain\States\PendingHRState;
use App\Modules\Request\Domain\States\RejectedState;
use App\Modules\Request\Domain\States\SubmittedState;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use App\Modules\Request\Domain\ValueObjects\RequestCode;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use App\Modules\Request\Infrastructure\Persistence\Models\RequestModel;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RequestTransitionMatrixTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[DataProvider('allowedTransitionProvider')]
    public function it_allows_normative_r07_transitions(string $from, string $to): void
    {
        $model = $this->persistRequestInState($from);

        expect($model->status->canTransitionTo($this->resolveStateClass($to)))->toBeTrue();
    }

    #[Test]
    #[DataProvider('forbiddenTransitionProvider')]
    public function it_blocks_non_normative_r07_transitions(string $from, string $to): void
    {
        $model = $this->persistRequestInState($from);

        expect($model->status->canTransitionTo($this->resolveStateClass($to)))->toBeFalse();
    }

    #[Test]
    public function it_marks_only_approved_rejected_and_cancelled_as_terminal_states(): void
    {
        $request = $this->sampleRequest(DraftState::$name);

        expect($request->isCancellable())->toBeTrue();
        expect($request->isTerminal())->toBeFalse();

        $submitted = $this->sampleRequest(SubmittedState::$name);
        expect($submitted->isCancellable())->toBeTrue();

        $rejected = $this->sampleRequest(RejectedState::$name);
        expect($rejected->isTerminal())->toBeTrue();
        expect($rejected->isCancellable())->toBeFalse();
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function allowedTransitionProvider(): array
    {
        return [
            'draft to submitted' => [DraftState::$name, SubmittedState::$name],
            'draft to cancelled' => [DraftState::$name, CancelledState::$name],
            'submitted to pending department manager' => [SubmittedState::$name, PendingDepartmentManagerState::$name],
            'submitted to cancelled' => [SubmittedState::$name, CancelledState::$name],
            'pending department manager to pending hr' => [PendingDepartmentManagerState::$name, PendingHRState::$name],
            'pending department manager to rejected' => [PendingDepartmentManagerState::$name, RejectedState::$name],
            'pending hr to pending dormitory manager' => [PendingHRState::$name, PendingDormitoryManagerState::$name],
            'pending dormitory manager to pending dormitory unit' => [PendingDormitoryManagerState::$name, PendingDormitoryUnitState::$name],
            'pending dormitory unit to approved' => [PendingDormitoryUnitState::$name, ApprovedState::$name],
            'pending dormitory unit to rejected' => [PendingDormitoryUnitState::$name, RejectedState::$name],
        ];
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function forbiddenTransitionProvider(): array
    {
        return [
            'draft to approved' => [DraftState::$name, ApprovedState::$name],
            'pending department manager to cancelled' => [PendingDepartmentManagerState::$name, CancelledState::$name],
            'approved to draft' => [ApprovedState::$name, DraftState::$name],
            'rejected to submitted' => [RejectedState::$name, SubmittedState::$name],
            'cancelled to draft' => [CancelledState::$name, DraftState::$name],
        ];
    }

    private function persistRequestInState(string $status): RequestModel
    {
        $model = new RequestModel([
            'code' => 'REQ-20260623-'.random_int(1000, 9999),
            'employee_id' => UuidGenerator::uuid7(),
            'dormitory_id' => UuidGenerator::uuid7(),
            'type' => RequestType::Personal,
            'check_in_date' => '2026-07-01',
            'check_out_date' => '2026-12-31',
            'status' => $status,
        ]);
        $model->save();

        return $model->refresh();
    }

    private function resolveStateClass(string $status): string
    {
        return match ($status) {
            DraftState::$name => DraftState::class,
            SubmittedState::$name => SubmittedState::class,
            PendingDepartmentManagerState::$name => PendingDepartmentManagerState::class,
            PendingHRState::$name => PendingHRState::class,
            PendingDormitoryManagerState::$name => PendingDormitoryManagerState::class,
            PendingDormitoryUnitState::$name => PendingDormitoryUnitState::class,
            ApprovedState::$name => ApprovedState::class,
            RejectedState::$name => RejectedState::class,
            CancelledState::$name => CancelledState::class,
            default => throw new \InvalidArgumentException('Unknown state: '.$status),
        };
    }

    private function sampleRequest(string $status): Request
    {
        return new Request(
            id: RequestId::fromString(UuidGenerator::uuid7()),
            code: RequestCode::fromString('REQ-20260623-0001'),
            employeeId: EmployeeReferenceId::fromString(UuidGenerator::uuid7()),
            dormitoryId: DormitorySiteId::fromString(UuidGenerator::uuid7()),
            type: RequestType::Personal,
            checkInDate: new DateTimeImmutable('2026-07-01', new DateTimeZone('UTC')),
            checkOutDate: new DateTimeImmutable('2026-12-31', new DateTimeZone('UTC')),
            status: $status,
        );
    }
}
