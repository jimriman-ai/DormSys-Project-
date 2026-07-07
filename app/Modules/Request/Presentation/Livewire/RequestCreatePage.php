<?php

declare(strict_types=1);

namespace App\Modules\Request\Presentation\Livewire;

use App\Modules\Request\Application\Services\CreatePersonalRequestAction;
use App\Modules\Request\Application\Services\RequestPrincipalEmployeeResolver;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use App\Support\Presentation\Concerns\HandlesUiMutationFeedback;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
final class RequestCreatePage extends Component
{
    use HandlesUiMutationFeedback;

    public string $dormitoryId = '';

    public string $checkInDate = '';

    public string $checkOutDate = '';

    public bool $submitting = false;

    public function save(
        CreatePersonalRequestAction $createPersonalRequest,
        RequestPrincipalEmployeeResolver $principalEmployee,
    ): void {
        $this->resetActionFeedback();
        $this->submitting = true;

        $validated = $this->validate([
            'dormitoryId' => ['required', 'uuid'],
            'checkInDate' => ['required', 'date_format:Y-m-d'],
            'checkOutDate' => ['required', 'date_format:Y-m-d', 'after:checkInDate'],
        ]);

        try {
            $created = $createPersonalRequest->execute(
                employeeId: EmployeeReferenceId::fromString($principalEmployee->requireEmployeeId()),
                dormitoryId: DormitorySiteId::fromString($validated['dormitoryId']),
                checkInDate: new DateTimeImmutable($validated['checkInDate'], new DateTimeZone('UTC')),
                checkOutDate: new DateTimeImmutable($validated['checkOutDate'], new DateTimeZone('UTC')),
            );

            $this->flashSuccess('درخواست با موفقیت ثبت شد.');

            $this->redirectRoute('requests.show', ['requestId' => $created->requireId()->value]);
        } catch (\Throwable $exception) {
            $this->captureMutationFailure($exception);
        } finally {
            $this->submitting = false;
        }
    }

    public function render(): View
    {
        return view('livewire.request.request-create-page');
    }
}
