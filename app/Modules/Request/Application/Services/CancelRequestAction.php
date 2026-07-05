<?php

declare(strict_types=1);

namespace App\Modules\Request\Application\Services;

use App\Modules\Request\Application\Contracts\RequestRepositoryContract;
use App\Modules\Request\Domain\Entities\Request;
use App\Modules\Request\Domain\Events\RequestCancelled;
use App\Modules\Request\Domain\Exceptions\InvalidRequestTransitionException;
use App\Modules\Request\Domain\Exceptions\RequestNotFoundException;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final class CancelRequestAction
{
    public function __construct(
        private readonly RequestRepositoryContract $requests,
    ) {}

    public function execute(RequestId $requestId): Request
    {
        $request = $this->requests->findById($requestId);

        if ($request === null) {
            throw new RequestNotFoundException('Request not found.');
        }

        if (! $request->isCancellable()) {
            throw new InvalidRequestTransitionException('Only draft or submitted requests can be cancelled.');
        }

        $previousStatus = $request->status;
        $cancelledAt = now('UTC')->toDateTimeImmutable();
        $cancelled = $request->markCancelled($cancelledAt);

        return DB::transaction(function () use ($cancelled, $previousStatus): Request {
            $persisted = $this->requests->save($cancelled);

            Event::dispatch(RequestCancelled::forRequest(
                requestId: $persisted->requireId()->value,
                previousStatus: $previousStatus,
            ));

            return $persisted;
        });
    }
}
