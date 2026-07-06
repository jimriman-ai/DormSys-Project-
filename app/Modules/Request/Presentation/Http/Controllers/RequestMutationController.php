<?php

declare(strict_types=1);

namespace App\Modules\Request\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Request\Application\Services\ApproveRequestStageAction;
use App\Modules\Request\Application\Services\CancelRequestAction;
use App\Modules\Request\Application\Services\RejectRequestAction;
use App\Modules\Request\Application\Services\SubmitRequestAction;
use App\Modules\Request\Domain\ValueObjects\ApproverReferenceId;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use App\Modules\Request\Presentation\Http\Requests\ApproveRequestStageRequest;
use App\Modules\Request\Presentation\Http\Requests\CancelRequestRequest;
use App\Modules\Request\Presentation\Http\Requests\RejectRequestStageRequest;
use App\Modules\Request\Presentation\Http\Requests\SubmitRequestRequest;
use App\Modules\Request\Presentation\Http\Responses\RequestApiResponseFactory;
use Illuminate\Http\JsonResponse;

final class RequestMutationController extends Controller
{
    public function __construct(
        private readonly SubmitRequestAction $submitRequest,
        private readonly CancelRequestAction $cancelRequest,
        private readonly ApproveRequestStageAction $approveRequestStage,
        private readonly RejectRequestAction $rejectRequest,
    ) {}

    public function submit(SubmitRequestRequest $request): JsonResponse
    {
        $requestId = RequestId::fromString((string) $request->validated('requestId'));

        return RequestApiResponseFactory::success(
            $this->submitRequest->execute($requestId),
        );
    }

    public function cancel(CancelRequestRequest $request): JsonResponse
    {
        $requestId = RequestId::fromString((string) $request->validated('requestId'));

        return RequestApiResponseFactory::success(
            $this->cancelRequest->execute($requestId),
        );
    }

    public function approve(ApproveRequestStageRequest $request): JsonResponse
    {
        $requestId = RequestId::fromString((string) $request->validated('requestId'));
        $approverId = ApproverReferenceId::fromString($this->mutationPrincipalId($request));

        return RequestApiResponseFactory::success(
            $this->approveRequestStage->execute($requestId, $approverId),
        );
    }

    public function reject(RejectRequestStageRequest $request): JsonResponse
    {
        $requestId = RequestId::fromString((string) $request->validated('requestId'));
        $approverId = ApproverReferenceId::fromString($this->mutationPrincipalId($request));

        return RequestApiResponseFactory::success(
            $this->rejectRequest->execute(
                $requestId,
                $approverId,
                (string) $request->validated('reason'),
            ),
        );
    }

    private function mutationPrincipalId(ApproveRequestStageRequest|RejectRequestStageRequest $request): string
    {
        return (string) $request->attributes->get('audit_principal_user_id');
    }
}
