<?php

declare(strict_types=1);

namespace App\Modules\Request\Presentation\Livewire;

use App\Modules\Request\Application\Contracts\RequestReadContract;
use App\Modules\Request\Application\Services\ApproveRequestStageAction;
use App\Modules\Request\Application\Services\CancelRequestAction;
use App\Modules\Request\Application\Services\RejectRequestAction;
use App\Modules\Request\Application\Services\RequestPrincipalEmployeeResolver;
use App\Modules\Request\Application\Services\SubmitRequestAction;
use App\Modules\Request\Domain\ValueObjects\ApproverReferenceId;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use App\Modules\Request\Presentation\Http\Responses\RequestApiResponseFactory;
use App\Support\Presentation\Concerns\HandlesUiMutationFeedback;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
final class RequestShowPage extends Component
{
    use HandlesUiMutationFeedback;

    public string $requestId;

    /** @var array<string, mixed> */
    public array $summary = [];

    /** @var list<array<string, mixed>> */
    public array $approvalHistory = [];

    public string $rejectReason = '';

    public bool $submitting = false;

    public function mount(
        string $requestId,
        RequestReadContract $requests,
        RequestPrincipalEmployeeResolver $principalEmployee,
    ): void {
        $this->requestId = $requestId;

        $summary = $requests->getRequestSummary(RequestId::fromString($requestId));

        if ($summary === null) {
            abort(404);
        }

        $principalEmployee->assertOwnsSummary($summary);

        $this->summary = RequestApiResponseFactory::serializeSummary($summary);
        $this->approvalHistory = array_map(
            static fn ($entry): array => [
                'stage' => $entry->stage,
                'decision' => $entry->decision,
                'approverId' => $entry->approverId,
                'reason' => $entry->reason,
                'decidedAt' => $entry->decidedAt,
            ],
            $requests->getApprovalHistory(RequestId::fromString($requestId)),
        );
    }

    public function submit(SubmitRequestAction $submitRequest, RequestReadContract $requests): void
    {
        $this->runMutation(fn () => $submitRequest->execute(RequestId::fromString($this->requestId)), $requests);
    }

    public function cancel(CancelRequestAction $cancelRequest, RequestReadContract $requests): void
    {
        $this->runMutation(fn () => $cancelRequest->execute(RequestId::fromString($this->requestId)), $requests);
    }

    public function approve(ApproveRequestStageAction $approveRequestStage, RequestReadContract $requests): void
    {
        $principalId = (string) request()->attributes->get('audit_principal_user_id');

        $this->runMutation(fn () => $approveRequestStage->execute(
            RequestId::fromString($this->requestId),
            ApproverReferenceId::fromString($principalId),
        ), $requests);
    }

    public function reject(RejectRequestAction $rejectRequest, RequestReadContract $requests): void
    {
        $validated = $this->validate([
            'rejectReason' => ['required', 'string', 'min:1'],
        ]);

        $principalId = (string) request()->attributes->get('audit_principal_user_id');

        $this->runMutation(fn () => $rejectRequest->execute(
            RequestId::fromString($this->requestId),
            ApproverReferenceId::fromString($principalId),
            $validated['rejectReason'],
        ), $requests);
    }

    /**
     * @param  callable(): \App\Modules\Request\Domain\Entities\Request  $mutation
     */
    private function runMutation(callable $mutation, RequestReadContract $requests): void
    {
        $this->resetActionFeedback();
        $this->submitting = true;

        try {
            $updated = $mutation();

            $this->summary = RequestApiResponseFactory::serialize($updated);
            $this->approvalHistory = array_map(
                static fn ($entry): array => [
                    'stage' => $entry->stage,
                    'decision' => $entry->decision,
                    'approverId' => $entry->approverId,
                    'reason' => $entry->reason,
                    'decidedAt' => $entry->decidedAt,
                ],
                $requests->getApprovalHistory(RequestId::fromString($this->requestId)),
            );
            $this->flashSuccess('عملیات با موفقیت انجام شد.');
        } catch (\Throwable $exception) {
            $this->captureMutationFailure($exception);
        } finally {
            $this->submitting = false;
        }
    }

    public function render(): View
    {
        return view('livewire.request.request-show-page');
    }
}
