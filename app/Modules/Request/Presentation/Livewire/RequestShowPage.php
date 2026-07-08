<?php

declare(strict_types=1);

namespace App\Modules\Request\Presentation\Livewire;

use App\Modules\Request\Application\Contracts\RequestReadContract;
use App\Modules\Request\Application\DTOs\RequestApprovalHistoryDTO;
use App\Modules\Request\Application\Services\RequestPrincipalEmployeeResolver;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use App\Modules\Request\Presentation\Http\Responses\RequestApiResponseFactory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
final class RequestShowPage extends Component
{
    public string $requestId = '';

    /** @var array<string, mixed> */
    public array $summary = [];

    /** @var list<array<string, mixed>> */
    public array $approvalHistory = [];

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

        $this->summary = self::mapSummary(
            RequestApiResponseFactory::serializeSummary($summary),
        );
        $this->approvalHistory = array_map(
            static fn (RequestApprovalHistoryDTO $entry): array => self::mapApprovalHistoryEntry($entry),
            $requests->getApprovalHistory(RequestId::fromString($requestId)),
        );
    }

    /**
     * @param  array<string, mixed>  $serialized
     * @return array<string, mixed>
     */
    private static function mapSummary(array $serialized): array
    {
        return [
            'request_id' => $serialized['id'],
            'request_code' => $serialized['code'],
            'request_status' => $serialized['status'],
            'request_type' => $serialized['type'],
            'dormitory_reference' => $serialized['dormitoryId'],
            'check_in_date' => $serialized['checkInDate'],
            'check_out_date' => $serialized['checkOutDate'],
            'submitted_at' => $serialized['submittedAt'],
            'cancelled_at' => $serialized['cancelledAt'],
            'rejection_reason' => $serialized['rejectionReason'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function mapApprovalHistoryEntry(RequestApprovalHistoryDTO $entry): array
    {
        return [
            'stage' => $entry->stage,
            'decision' => $entry->decision,
            'approver_reference' => $entry->approverId,
            'decision_reason' => $entry->reason,
            'decided_at' => $entry->decidedAt,
        ];
    }

    public function render(): View
    {
        return view('livewire.request.request-show-page');
    }
}
