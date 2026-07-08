<div>
    <x-ui.page-header :title="'درخواست '.$summary['request_code']" description="جزئیات درخواست بر اساس وضعیت ثبت‌شده در سامانه">
        <x-slot:actions>
            <a href="{{ route('requests.index') }}" class="text-sm text-slate-600 hover:text-slate-900" wire:navigate>
                بازگشت به فهرست
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <section class="space-y-4 rounded-xl border border-slate-200 bg-white p-6">
        <h2 class="text-lg font-semibold text-slate-900">اطلاعات درخواست</h2>

        <dl class="grid gap-4 sm:grid-cols-2 text-sm">
            <div>
                <dt class="text-slate-500">وضعیت</dt>
                <dd class="mt-1 font-medium text-slate-900">{{ $summary['request_status'] }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">نوع</dt>
                <dd class="mt-1 font-medium text-slate-900">{{ $summary['request_type'] }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">شناسه خوابگاه</dt>
                <dd class="mt-1 font-medium text-slate-900" dir="ltr">{{ $summary['dormitory_reference'] }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">تاریخ ورود</dt>
                <dd class="mt-1 font-medium text-slate-900">{{ $summary['check_in_date'] }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">تاریخ خروج</dt>
                <dd class="mt-1 font-medium text-slate-900">{{ $summary['check_out_date'] }}</dd>
            </div>
            @if ($summary['submitted_at'])
                <div>
                    <dt class="text-slate-500">زمان ارسال</dt>
                    <dd class="mt-1 font-medium text-slate-900" dir="ltr">{{ $summary['submitted_at'] }}</dd>
                </div>
            @endif
            @if ($summary['cancelled_at'])
                <div>
                    <dt class="text-slate-500">زمان لغو</dt>
                    <dd class="mt-1 font-medium text-slate-900" dir="ltr">{{ $summary['cancelled_at'] }}</dd>
                </div>
            @endif
            @if ($summary['rejection_reason'])
                <div class="sm:col-span-2">
                    <dt class="text-slate-500">دلیل رد</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $summary['rejection_reason'] }}</dd>
                </div>
            @endif
        </dl>
    </section>

    <section class="mt-6 rounded-xl border border-slate-200 bg-white p-6">
        <h2 class="mb-4 text-lg font-semibold text-slate-900">سوابق تأیید</h2>

        @if ($approvalHistory === [])
            <x-ui.empty-state
                title="سابقه‌ای ثبت نشده است"
                description="هنوز مرحله تأییدی برای این درخواست ثبت نشده است."
            />
        @else
            <div class="overflow-hidden rounded-lg border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-4 py-3 text-right font-medium">مرحله</th>
                            <th class="px-4 py-3 text-right font-medium">نتیجه</th>
                            <th class="px-4 py-3 text-right font-medium">تأییدکننده</th>
                            <th class="px-4 py-3 text-right font-medium">زمان</th>
                            <th class="px-4 py-3 text-right font-medium">دلیل</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($approvalHistory as $entry)
                            <tr wire:key="approval-{{ $entry['stage'] }}-{{ $entry['decided_at'] }}">
                                <td class="px-4 py-3">{{ $entry['stage'] }}</td>
                                <td class="px-4 py-3">{{ $entry['decision'] }}</td>
                                <td class="px-4 py-3" dir="ltr">{{ $entry['approver_reference'] }}</td>
                                <td class="px-4 py-3" dir="ltr">{{ $entry['decided_at'] }}</td>
                                <td class="px-4 py-3">{{ $entry['decision_reason'] ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
