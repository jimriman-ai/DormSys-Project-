<?php

declare(strict_types=1);

namespace App\Modules\Request\Presentation\Livewire;

use App\Modules\Request\Application\Contracts\RequestRepositoryContract;
use App\Modules\Request\Application\Services\ApproveStage1RequestAction;
use App\Modules\Request\Application\Services\RejectStage1RequestAction;
use App\Modules\Request\Domain\Entities\Request;
use App\Modules\Request\Domain\ValueObjects\ApproverReferenceId;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use App\Shared\Auth\IdentityRoleGuard;
use App\Support\Presentation\Concerns\HandlesUiMutationFeedback;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * [PERMIT-ID: IMPL-PERMIT-03] Stage-1 Approver Console — approve/reject via Application Actions.
 * Gate role: dormitory-manager (DGAP-13 / Lead DGAP-09 scoped).
 * F-W07-04 implementation lock: list/filter/polish under /approvals/stage1.
 */
#[Layout('components.layouts.app')]
final class Stage1ApproverConsolePage extends Component
{
    use HandlesUiMutationFeedback;

    public ?string $requestId = null;

    public string $rejectionReason = '';

    public string $search = '';

    /**
     * Presentation rows for the pending Stage-1 queue (Livewire-serializable).
     *
     * @var Collection<int, array{id: string, code: string, employee_id: string, submitted_at: string|null}>
     */
    public Collection $pendingRequests;

    public function mount(?string $requestId = null): void
    {
        IdentityRoleGuard::assertDormitoryManager();
        $this->requestId = $requestId;
        $this->pendingRequests = collect();

        if ($this->requestId === null || $this->requestId === '') {
            $this->loadRequests();
        }
    }

    public function updatedSearch(): void
    {
        $this->loadRequests();
    }

    public function loadRequests(): void
    {
        /** @var RequestRepositoryContract $repository */
        $repository = app(RequestRepositoryContract::class);

        $pending = $repository->listPendingStage1();
        $needle = trim($this->search);

        if ($needle !== '') {
            $pending = $pending->filter(function (Request $request) use ($needle): bool {
                $id = $request->id?->value ?? '';
                $code = (string) $request->code;
                $employeeId = $request->employeeId->value;

                return str_contains($id, $needle)
                    || str_contains($code, $needle)
                    || str_contains($employeeId, $needle);
            })->values();
        }

        $this->pendingRequests = $pending->map(static function (Request $request): array {
            return [
                'id' => $request->requireId()->value,
                'code' => (string) $request->code,
                'employee_id' => $request->employeeId->value,
                'submitted_at' => $request->submittedAt?->format('Y-m-d H:i'),
            ];
        })->values();
    }

    public function approve(ApproveStage1RequestAction $approveStage1): void
    {
        $this->resetActionFeedback();

        if ($this->requestId === null || $this->requestId === '') {
            $this->addError('requestId', 'شناسه درخواست الزامی است.');

            return;
        }

        try {
            $approveStage1->execute(
                RequestId::fromString($this->requestId),
                $this->approverReferenceId(),
            );
            $this->flashSuccess('درخواست در مرحله یک تأیید شد.');
        } catch (\Throwable $exception) {
            $this->captureMutationFailure($exception);
        }
    }

    public function reject(RejectStage1RequestAction $rejectStage1): void
    {
        $this->resetActionFeedback();

        if ($this->requestId === null || $this->requestId === '') {
            $this->addError('requestId', 'شناسه درخواست الزامی است.');

            return;
        }

        $validated = $this->validate([
            'rejectionReason' => ['required', 'string', 'min:3'],
        ]);

        try {
            $rejectStage1->execute(
                RequestId::fromString($this->requestId),
                $this->approverReferenceId(),
                $validated['rejectionReason'],
            );
            $this->flashSuccess('درخواست رد شد.');
        } catch (\Throwable $exception) {
            $this->captureMutationFailure($exception);
        }
    }

    public function render(): View
    {
        IdentityRoleGuard::assertDormitoryManager();

        return view('livewire.request.stage1-approver-console-page', [
            'requestId' => $this->requestId,
        ]);
    }

    private function approverReferenceId(): ApproverReferenceId
    {
        $identityId = auth('identity')->id();

        if (! is_string($identityId) || $identityId === '') {
            abort(403);
        }

        return ApproverReferenceId::fromString($identityId);
    }
}
