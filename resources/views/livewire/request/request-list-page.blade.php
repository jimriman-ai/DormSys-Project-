<div wire:init="refreshList">
    <x-ui.page-header title="درخواست‌های من" description="فهرست درخواست‌های ثبت‌شده شما">
        <x-slot:actions>
            <a
                href="{{ route('requests.create') }}"
                class="inline-flex items-center justify-center rounded-lg bg-sky-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-sky-800"
                wire:navigate
            >
                ثبت درخواست جدید
            </a>
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
            <x-ui.alert type="error" :message="$loadError ?? 'بارگذاری فهرست درخواست‌ها با خطا مواجه شد.'" />

            <x-ui.button type="button" wire:click="refreshList" wire:loading.attr="disabled" wire:target="refreshList">
                تلاش مجدد
            </x-ui.button>
        </div>
    @elseif ($uiState === 'empty')
        <x-ui.empty-state
            title="درخواستی ثبت نشده است"
            description="هنوز درخواستی برای نمایش وجود ندارد."
        >
            <x-slot:action>
                <a
                    href="{{ route('requests.create') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-sky-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-sky-800"
                    wire:navigate
                >
                    ثبت درخواست جدید
                </a>
            </x-slot:action>
        </x-ui.empty-state>
    @else
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 text-right font-medium">کد</th>
                        <th class="px-4 py-3 text-right font-medium">نوع</th>
                        <th class="px-4 py-3 text-right font-medium">وضعیت</th>
                        <th class="px-4 py-3 text-right font-medium">شناسه خوابگاه</th>
                        <th class="px-4 py-3 text-right font-medium">تاریخ ورود</th>
                        <th class="px-4 py-3 text-right font-medium">تاریخ خروج</th>
                        <th class="px-4 py-3 text-right font-medium">تاریخ ثبت</th>
                        <th class="px-4 py-3 text-right font-medium">مشاهده</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($requests as $request)
                        <tr wire:key="request-{{ $request['id'] }}">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $request['code'] }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $request['type'] }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $request['status'] }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $request['dormitory_id'] }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $request['check_in_date'] }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $request['check_out_date'] }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $request['submitted_at'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-700">
                                <a
                                    href="{{ route('requests.show', ['requestId' => $request['id']]) }}"
                                    class="text-sm text-slate-600 hover:text-slate-900"
                                    wire:navigate
                                >
                                    مشاهده
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
