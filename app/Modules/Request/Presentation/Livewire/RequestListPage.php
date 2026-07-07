<?php

declare(strict_types=1);

namespace App\Modules\Request\Presentation\Livewire;

use App\Modules\Request\Application\Contracts\RequestReadContract;
use App\Modules\Request\Application\Services\RequestPrincipalEmployeeResolver;
use App\Modules\Request\Presentation\Http\Responses\RequestApiResponseFactory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('components.layouts.app')]
final class RequestListPage extends Component
{
    public string $uiState = 'loading';

    public ?string $loadError = null;

    /** @var list<array<string, mixed>> */
    public array $requests = [];

    public function refreshList(
        RequestReadContract $requests,
        RequestPrincipalEmployeeResolver $principalEmployee,
    ): void {
        $this->uiState = 'loading';
        $this->loadError = null;

        try {
            $employeeId = $principalEmployee->requireEmployeeId();

            $this->requests = array_map(
                static fn ($summary): array => self::mapContractRow(
                    RequestApiResponseFactory::serializeSummary($summary),
                ),
                $requests->listByEmployee($employeeId),
            );

            $this->uiState = $this->requests === [] ? 'empty' : 'ready';
        } catch (Throwable $exception) {
            $this->requests = [];
            $this->loadError = $exception->getMessage();
            $this->uiState = 'error';
        }
    }

    public function render(): View
    {
        return view('livewire.request.request-list-page');
    }

    /**
     * @param  array<string, mixed>  $summary
     * @return array<string, mixed>
     */
    private static function mapContractRow(array $summary): array
    {
        return [
            'id' => $summary['id'],
            'code' => $summary['code'],
            'type' => $summary['type'],
            'status' => $summary['status'],
            'dormitory_id' => $summary['dormitoryId'],
            'check_in_date' => $summary['checkInDate'],
            'check_out_date' => $summary['checkOutDate'],
            'submitted_at' => $summary['submittedAt'],
        ];
    }
}
