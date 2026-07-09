<div wire:init="refreshList">
    <x-ui.page-header title="اعلان‌های من" description="فهرست اعلان‌های دریافتی شما">
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

    @if ($actionError)
        <x-ui.alert type="error" :message="$actionError" class="mb-4" />
    @endif

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
            <x-ui.alert type="error" :message="$loadError ?? 'بارگذاری فهرست اعلان‌ها با خطا مواجه شد.'" />

            <x-ui.button type="button" wire:click="refreshList" wire:loading.attr="disabled" wire:target="refreshList">
                تلاش مجدد
            </x-ui.button>
        </div>
    @elseif ($uiState === 'empty')
        <x-ui.empty-state
            title="اعلانی وجود ندارد"
            description="هنوز اعلانی برای نمایش وجود ندارد."
        />
    @else
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 text-right font-medium">عنوان</th>
                        <th class="px-4 py-3 text-right font-medium">پیام</th>
                        <th class="px-4 py-3 text-right font-medium">نوع</th>
                        <th class="px-4 py-3 text-right font-medium">اولویت</th>
                        <th class="px-4 py-3 text-right font-medium">تاریخ ایجاد</th>
                        <th class="px-4 py-3 text-right font-medium">وضعیت</th>
                        <th class="px-4 py-3 text-right font-medium">عملیات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($notifications as $notification)
                        <tr wire:key="notification-{{ $notification['id'] }}">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $notification['title'] }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $notification['message'] }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $notification['notification_type'] }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $notification['priority'] }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $notification['created_at'] }}</td>
                            <td class="px-4 py-3 text-slate-700">
                                {{ $notification['is_read'] ? 'خوانده شده' : 'خوانده نشده' }}
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if (! $notification['is_read'])
                                        <x-ui.button
                                            type="button"
                                            variant="secondary"
                                            wire:click="markNotificationRead('{{ $notification['id'] }}')"
                                            wire:loading.attr="disabled"
                                            wire:target="markNotificationRead"
                                        >
                                            <span wire:loading.remove wire:target="markNotificationRead">علامت‌گذاری به‌عنوان خوانده‌شده</span>
                                            <span wire:loading wire:target="markNotificationRead">در حال علامت‌گذاری...</span>
                                        </x-ui.button>
                                    @endif
                                    @if ($notification['request_show_url'] !== null)
                                        <a
                                            href="{{ $notification['request_show_url'] }}"
                                            class="text-sm text-slate-600 hover:text-slate-900"
                                            wire:navigate
                                        >
                                            مشاهده
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
