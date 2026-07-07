<?php

declare(strict_types=1);

namespace App\Modules\Request\Presentation\Livewire;

use App\Modules\Request\Application\Contracts\RequestReadContract;
use App\Modules\Request\Application\Services\RequestPrincipalEmployeeResolver;
use App\Modules\Request\Presentation\Http\Responses\RequestApiResponseFactory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
final class RequestListPage extends Component
{
    /** @var list<array<string, mixed>> */
    public array $requests = [];

    public function mount(
        RequestReadContract $requests,
        RequestPrincipalEmployeeResolver $principalEmployee,
    ): void {
        $employeeId = $principalEmployee->requireEmployeeId();

        $this->requests = array_map(
            static fn ($summary): array => RequestApiResponseFactory::serializeSummary($summary),
            $requests->listByEmployee($employeeId),
        );
    }

    public function render(): View
    {
        return view('livewire.request.request-list-page');
    }
}
