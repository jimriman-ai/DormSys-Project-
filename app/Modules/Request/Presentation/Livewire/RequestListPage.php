<?php

declare(strict_types=1);

namespace App\Modules\Request\Presentation\Livewire;

use App\Modules\Request\Application\Contracts\RequestReadContract;
use App\Modules\Request\Application\DTOs\EmployeeRequestListQueryDTO;
use App\Modules\Request\Application\DTOs\RequestEmployeeListFilterOptions;
use App\Modules\Request\Application\Services\RequestPrincipalEmployeeResolver;
use App\Modules\Request\Presentation\Http\Responses\RequestApiResponseFactory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Throwable;

#[Layout('components.layouts.app')]
final class RequestListPage extends Component
{
    private const int PER_PAGE = 15;

    public string $uiState = 'loading';

    public ?string $loadError = null;

    #[Url(as: 'status', except: '')]
    public ?string $statusFilter = null;

    #[Url(as: 'sort')]
    public string $sortField = 'submitted_at';

    #[Url(as: 'dir')]
    public string $sortDirection = 'desc';

    #[Url(as: 'page', except: 1)]
    public int $page = 1;

    public int $total = 0;

    public int $lastPage = 1;

    public int $perPage = self::PER_PAGE;

    /** @var list<string> */
    public array $statusOptions = [];

    /** @var list<array<string, mixed>> */
    public array $requests = [];

    public function updatedStatusFilter(): void
    {
        $this->page = 1;
        $this->refreshList(
            app(RequestReadContract::class),
            app(RequestPrincipalEmployeeResolver::class),
        );
    }

    public function updatedSortField(): void
    {
        $this->page = 1;
        $this->refreshList(
            app(RequestReadContract::class),
            app(RequestPrincipalEmployeeResolver::class),
        );
    }

    public function updatedSortDirection(): void
    {
        $this->page = 1;
        $this->refreshList(
            app(RequestReadContract::class),
            app(RequestPrincipalEmployeeResolver::class),
        );
    }

    public function updatedPage(): void
    {
        $this->refreshList(
            app(RequestReadContract::class),
            app(RequestPrincipalEmployeeResolver::class),
        );
    }

    public function clearFilters(): void
    {
        $this->statusFilter = null;
        $this->sortField = 'submitted_at';
        $this->sortDirection = 'desc';
        $this->page = 1;
        $this->refreshList(
            app(RequestReadContract::class),
            app(RequestPrincipalEmployeeResolver::class),
        );
    }

    public function goToPage(int $page): void
    {
        $this->page = max($page, 1);
        $this->refreshList(
            app(RequestReadContract::class),
            app(RequestPrincipalEmployeeResolver::class),
        );
    }

    public function refreshList(
        RequestReadContract $requests,
        RequestPrincipalEmployeeResolver $principalEmployee,
    ): void {
        $this->uiState = 'loading';
        $this->loadError = null;

        try {
            $employeeId = $principalEmployee->requireEmployeeId();
            $this->normalizeListState();

            $result = $requests->listByEmployeePaginated(new EmployeeRequestListQueryDTO(
                employeeId: $employeeId,
                status: $this->statusFilter,
                sortField: $this->sortField,
                sortDirection: $this->sortDirection,
                page: $this->page,
                perPage: self::PER_PAGE,
            ));

            $this->page = $result->currentPage;
            $this->total = $result->total;
            $this->lastPage = $result->lastPage;
            $this->perPage = $result->perPage;
            $this->statusOptions = $result->statusOptions;

            $this->requests = array_map(
                static fn ($summary): array => self::mapContractRow(
                    RequestApiResponseFactory::serializeSummary($summary),
                ),
                $result->items,
            );

            $this->uiState = $this->total === 0 && $this->statusFilter === null
                ? 'empty'
                : 'ready';
        } catch (Throwable $exception) {
            $this->requests = [];
            $this->total = 0;
            $this->lastPage = 1;
            $this->statusOptions = [];
            $this->loadError = $exception->getMessage();
            $this->uiState = 'error';
        }
    }

    public function render(): View
    {
        return view('livewire.request.request-list-page');
    }

    private function normalizeListState(): void
    {
        if ($this->statusFilter === '') {
            $this->statusFilter = null;
        }

        if (! in_array($this->sortField, RequestEmployeeListFilterOptions::SORT_FIELDS, true)) {
            $this->sortField = 'submitted_at';
        }

        if (! in_array($this->sortDirection, ['asc', 'desc'], true)) {
            $this->sortDirection = 'desc';
        }

        if ($this->page < 1) {
            $this->page = 1;
        }

        if ($this->statusFilter !== null && ! in_array($this->statusFilter, RequestEmployeeListFilterOptions::statusValues(), true)) {
            $this->statusFilter = null;
        }
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
