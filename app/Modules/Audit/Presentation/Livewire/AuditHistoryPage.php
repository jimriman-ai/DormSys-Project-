<?php

declare(strict_types=1);

namespace App\Modules\Audit\Presentation\Livewire;

use App\Modules\Audit\Application\Contracts\AuditEventTypeCatalogPort;
use App\Modules\Audit\Application\Contracts\AuditHistoryReadContract;
use App\Modules\Audit\Application\DTOs\AuditHistoryItemDto;
use App\Modules\Audit\Application\DTOs\AuditHistoryQuery;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Morilog\Jalali\Jalalian;
use Throwable;

#[Layout('components.layouts.app')]
final class AuditHistoryPage extends Component
{
    private const int PER_PAGE = 50;

    public string $uiState = 'loading';

    public ?string $loadError = null;

    #[Url(as: 'page', except: 1)]
    public int $page = 1;

    public int $total = 0;

    public int $lastPage = 1;

    public int $perPage = self::PER_PAGE;

    /** @var list<array<string, mixed>> */
    public array $entries = [];

    public function updatedPage(
        AuditHistoryReadContract $historyRead,
        AuditEventTypeCatalogPort $eventTypes,
    ): void {
        $this->refreshList($historyRead, $eventTypes);
    }

    public function goToPage(
        int $page,
        AuditHistoryReadContract $historyRead,
        AuditEventTypeCatalogPort $eventTypes,
    ): void {
        $this->page = max($page, 1);
        $this->refreshList($historyRead, $eventTypes);
    }

    public function refreshList(
        AuditHistoryReadContract $historyRead,
        AuditEventTypeCatalogPort $eventTypes,
    ): void {
        $this->uiState = 'loading';
        $this->loadError = null;

        try {
            if ($this->page < 1) {
                $this->page = 1;
            }

            // Fixed v1 query profile (OQ-AU-01): satisfy Spec10 filter-dimension
            // requirement without exposing filter/search UI controls.
            $result = $historyRead->query(new AuditHistoryQuery(
                eventTypes: $eventTypes->allEventTypeValues(),
                page: $this->page,
                perPage: self::PER_PAGE,
            ));

            $this->page = $result->page;
            $this->total = $result->total;
            $this->lastPage = $result->lastPage;
            $this->perPage = $result->perPage;

            $this->entries = array_map(
                static fn (AuditHistoryItemDto $item): array => self::mapHistoryRow($item),
                $result->items,
            );

            $this->uiState = $this->total === 0 ? 'empty' : 'ready';
        } catch (Throwable $exception) {
            $this->entries = [];
            $this->total = 0;
            $this->lastPage = 1;
            $this->loadError = $exception->getMessage();
            $this->uiState = 'error';
        }
    }

    public function render(): View
    {
        return view('livewire.audit.audit-history-page');
    }

    /**
     * @return array<string, mixed>
     */
    private static function mapHistoryRow(AuditHistoryItemDto $item): array
    {
        return [
            'audit_log_id' => $item->auditLogId,
            'correlation_id' => $item->correlationId,
            'event_type' => $item->eventType,
            'entity_type' => $item->entityType,
            'entity_id' => $item->entityId,
            'actor_type' => $item->actorType,
            'actor_id' => $item->actorId,
            'source_context' => $item->sourceContext,
            'old_values' => self::formatJsonPayload($item->oldValues),
            'new_values' => self::formatJsonPayload($item->newValues),
            'metadata' => self::formatJsonPayload($item->metadata),
            'occurred_at' => (string) Jalalian::fromDateTime($item->occurredAt)->format('Y/m/d H:i'),
            'created_at' => (string) Jalalian::fromDateTime($item->createdAt)->format('Y/m/d H:i'),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $payload
     */
    private static function formatJsonPayload(?array $payload): ?string
    {
        if ($payload === null) {
            return null;
        }

        return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
