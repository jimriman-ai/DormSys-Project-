<div wire:init="refreshList">
    <x-ui.page-header title="تاریخچه حسابرسی" description="نمایش فقط‌خواندنی رخدادهای حسابرسی ثبت‌شده">
        <x-slot:actions>
            <x-ui.button
                type="button"
                variant="secondary"
                wire:click="refreshList"
                wire:loading.attr="disabled"
                wire:target="refreshList"
            >
                <span wire:loading.remove wire:target="refreshList">بروزرسانی</span>
                <span wire:loading wire:target="refreshList">در حال بروزرسانی...</span>
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($uiState === 'loading')
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white" aria-busy="true" aria-live="polite">
            <div class="animate-pulse space-y-3 p-6">
                <div class="h-4 w-1/3 rounded bg-slate-200"></div>
                <div class="h-4 w-full rounded bg-slate-100"></div>
                <div class="h-4 w-full rounded bg-slate-100"></div>
                <div class="h-4 w-2/3 rounded bg-slate-100"></div>
            </div>
        </div>
    @elseif ($uiState === 'error')
        <div class="space-y-4">
            <x-ui.alert type="error" :message="$loadError ?? 'بارگذاری تاریخچه حسابرسی با خطا مواجه شد.'" />

            <x-ui.button type="button" wire:click="refreshList" wire:loading.attr="disabled" wire:target="refreshList">
                تلاش مجدد
            </x-ui.button>
        </div>
    @elseif ($uiState === 'empty')
        <x-ui.empty-state
            title="رکوردی وجود ندارد"
            description="هنوز رخداد حسابرسی‌ای برای نمایش وجود ندارد."
        />
    @else
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 text-right font-medium">نوع رخداد</th>
                        <th class="px-4 py-3 text-right font-medium">موجودیت</th>
                        <th class="px-4 py-3 text-right font-medium">شناسه موجودیت</th>
                        <th class="px-4 py-3 text-right font-medium">عامل</th>
                        <th class="px-4 py-3 text-right font-medium">شناسه عامل</th>
                        <th class="px-4 py-3 text-right font-medium">منبع</th>
                        <th class="px-4 py-3 text-right font-medium">همبستگی</th>
                        <th class="px-4 py-3 text-right font-medium">زمان وقوع</th>
                        <th class="px-4 py-3 text-right font-medium">زمان ثبت</th>
                        <th class="px-4 py-3 text-right font-medium">مقادیر قبلی</th>
                        <th class="px-4 py-3 text-right font-medium">مقادیر جدید</th>
                        <th class="px-4 py-3 text-right font-medium">فراداده</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($entries as $entry)
                        <tr wire:key="audit-{{ $entry['audit_log_id'] }}">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $entry['event_type'] }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $entry['entity_type'] }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-700">{{ $entry['entity_id'] }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $entry['actor_type'] }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-700">{{ $entry['actor_id'] }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $entry['source_context'] }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-700">{{ $entry['correlation_id'] }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $entry['occurred_at'] }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $entry['created_at'] }}</td>
                            <td class="max-w-xs truncate px-4 py-3 font-mono text-xs text-slate-700" title="{{ $entry['old_values'] }}">
                                {{ $entry['old_values'] ?? '—' }}
                            </td>
                            <td class="max-w-xs truncate px-4 py-3 font-mono text-xs text-slate-700" title="{{ $entry['new_values'] }}">
                                {{ $entry['new_values'] ?? '—' }}
                            </td>
                            <td class="max-w-xs truncate px-4 py-3 font-mono text-xs text-slate-700" title="{{ $entry['metadata'] }}">
                                {{ $entry['metadata'] ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($lastPage > 1)
            <div class="mt-4 flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                <span>صفحه {{ $page }} از {{ $lastPage }}</span>
                <div class="flex gap-2">
                    <x-ui.button
                        type="button"
                        variant="secondary"
                        wire:click="goToPage({{ max($page - 1, 1) }})"
                        :disabled="$page <= 1"
                    >
                        قبلی
                    </x-ui.button>
                    <x-ui.button
                        type="button"
                        variant="secondary"
                        wire:click="goToPage({{ min($page + 1, $lastPage) }})"
                        :disabled="$page >= $lastPage"
                    >
                        بعدی
                    </x-ui.button>
                </div>
            </div>
        @endif
    @endif
</div>
