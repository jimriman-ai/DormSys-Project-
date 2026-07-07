<div>
    <x-ui.page-header :title="'درخواست '.$summary['code']" description="جزئیات درخواست بر اساس وضعیت ثبت‌شده در سامانه">
        <x-slot:actions>
            <a href="{{ route('requests.index') }}" class="text-sm text-slate-600 hover:text-slate-900" wire:navigate>
                بازگشت به فهرست
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($actionError)
        <x-ui.alert type="error" :message="$actionError" class="mb-4" />
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="space-y-4 rounded-xl border border-slate-200 bg-white p-6 lg:col-span-2">
            <h2 class="text-lg font-semibold text-slate-900">اطلاعات درخواست</h2>

            <dl class="grid gap-4 sm:grid-cols-2 text-sm">
                <div>
                    <dt class="text-slate-500">وضعیت</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $summary['status'] }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">نوع</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $summary['type'] }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">شناسه خوابگاه</dt>
                    <dd class="mt-1 font-medium text-slate-900" dir="ltr">{{ $summary['dormitoryId'] }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">تاریخ ورود</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $summary['checkInDate'] }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">تاریخ خروج</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $summary['checkOutDate'] }}</dd>
                </div>
                @if ($summary['submittedAt'])
                    <div>
                        <dt class="text-slate-500">زمان ارسال</dt>
                        <dd class="mt-1 font-medium text-slate-900" dir="ltr">{{ $summary['submittedAt'] }}</dd>
                    </div>
                @endif
                @if ($summary['cancelledAt'])
                    <div>
                        <dt class="text-slate-500">زمان لغو</dt>
                        <dd class="mt-1 font-medium text-slate-900" dir="ltr">{{ $summary['cancelledAt'] }}</dd>
                    </div>
                @endif
                @if ($summary['rejectionReason'])
                    <div class="sm:col-span-2">
                        <dt class="text-slate-500">دلیل رد</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ $summary['rejectionReason'] }}</dd>
                    </div>
                @endif
            </dl>
        </section>

        <section class="space-y-4 rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="text-lg font-semibold text-slate-900">عملیات</h2>
            <p class="text-sm text-slate-600">
                اعتبار هر عملیات توسط سامانه بررسی می‌شود.
            </p>

            <div class="space-y-2">
                <x-ui.button
                    type="button"
                    class="w-full"
                    wire:click="submit"
                    wire:loading.attr="disabled"
                    wire:target="submit,cancel,approve,reject"
                >
                    ارسال
                </x-ui.button>

                <x-ui.button
                    type="button"
                    variant="secondary"
                    class="w-full"
                    wire:click="cancel"
                    wire:loading.attr="disabled"
                    wire:target="submit,cancel,approve,reject"
                >
                    لغو
                </x-ui.button>

                <x-ui.button
                    type="button"
                    class="w-full"
                    wire:click="approve"
                    wire:loading.attr="disabled"
                    wire:target="submit,cancel,approve,reject"
                >
                    تأیید
                </x-ui.button>
            </div>

            <div class="space-y-2 border-t border-slate-100 pt-4">
                <x-ui.form-field label="دلیل رد" for="rejectReason" :error="$errors->first('rejectReason')">
                    <textarea
                        id="rejectReason"
                        wire:model="rejectReason"
                        rows="3"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                    ></textarea>
                </x-ui.form-field>

                <x-ui.button
                    type="button"
                    variant="danger"
                    class="w-full"
                    wire:click="reject"
                    wire:loading.attr="disabled"
                    wire:target="submit,cancel,approve,reject"
                >
                    رد
                </x-ui.button>
            </div>
        </section>
    </div>

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
                            <tr wire:key="approval-{{ $entry['stage'] }}-{{ $entry['decidedAt'] }}">
                                <td class="px-4 py-3">{{ $entry['stage'] }}</td>
                                <td class="px-4 py-3">{{ $entry['decision'] }}</td>
                                <td class="px-4 py-3" dir="ltr">{{ $entry['approverId'] }}</td>
                                <td class="px-4 py-3" dir="ltr">{{ $entry['decidedAt'] }}</td>
                                <td class="px-4 py-3">{{ $entry['reason'] ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
