<div>
    <x-ui.page-header title="درخواست‌های من" description="فهرست درخواست‌های ثبت‌شده شما">
        <x-slot:actions>
            <a href="{{ route('requests.create') }}">
                <x-ui.button>ثبت درخواست جدید</x-ui.button>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($requests === [])
        <x-ui.empty-state
            title="درخواستی ثبت نشده است"
            description="برای شروع، یک درخواست جدید ثبت کنید."
        >
            <x-slot:action>
                <a href="{{ route('requests.create') }}">
                    <x-ui.button>ثبت درخواست جدید</x-ui.button>
                </a>
            </x-slot:action>
        </x-ui.empty-state>
    @else
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 text-right font-medium">کد</th>
                        <th class="px-4 py-3 text-right font-medium">وضعیت</th>
                        <th class="px-4 py-3 text-right font-medium">نوع</th>
                        <th class="px-4 py-3 text-right font-medium">تاریخ ورود</th>
                        <th class="px-4 py-3 text-right font-medium">تاریخ خروج</th>
                        <th class="px-4 py-3 text-right font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($requests as $request)
                        <tr wire:key="request-{{ $request['id'] }}">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $request['code'] }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $request['status'] }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $request['type'] }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $request['checkInDate'] }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $request['checkOutDate'] }}</td>
                            <td class="px-4 py-3 text-left">
                                <a
                                    href="{{ route('requests.show', $request['id']) }}"
                                    class="text-sky-700 hover:text-sky-900"
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
