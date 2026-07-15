<div>
    <h1 class="text-xl font-semibold text-slate-900">داشبورد مدیر خوابگاه</h1>

    <section class="mt-6" aria-labelledby="dormitory-list-heading">
        <h2 id="dormitory-list-heading" class="text-base font-semibold text-slate-800">خوابگاه‌های اختصاص‌یافته</h2>

        @if (count($dormitories) === 0)
            <p class="mt-3 text-sm text-slate-600" data-testid="dormitory-manager-empty">
                خوابگاهی به شما اختصاص داده نشده است.
            </p>
        @else
            <ul class="mt-4 space-y-4">
                @foreach ($dormitories as $dormitory)
                    @php
                        $ratio = $dormitory['bed_total'] > 0
                            ? (int) round(($dormitory['bed_occupied'] / $dormitory['bed_total']) * 100)
                            : 0;
                    @endphp
                    <li
                        class="rounded-xl border border-slate-200 bg-white p-4"
                        data-testid="dormitory-card"
                        data-dormitory-id="{{ $dormitory['id'] }}"
                    >
                        <h3 class="text-base font-semibold text-slate-900">{{ $dormitory['name'] }}</h3>

                        <dl class="mt-3 grid grid-cols-2 gap-2 text-sm text-slate-700 sm:grid-cols-4">
                            <div>
                                <dt class="text-slate-500">واحدها (اتاق)</dt>
                                <dd data-testid="unit-count">{{ $dormitory['unit_count'] }}</dd>
                            </div>
                            <div>
                                <dt class="text-slate-500">تخت کل</dt>
                                <dd data-testid="bed-total">{{ $dormitory['bed_total'] }}</dd>
                            </div>
                            <div>
                                <dt class="text-slate-500">اشغال‌شده</dt>
                                <dd data-testid="bed-occupied">{{ $dormitory['bed_occupied'] }}</dd>
                            </div>
                            <div>
                                <dt class="text-slate-500">آزاد</dt>
                                <dd data-testid="bed-available">{{ $dormitory['bed_available'] }}</dd>
                            </div>
                        </dl>

                        <div class="mt-4" data-testid="occupancy-ratio">
                            <div class="mb-1 flex justify-between text-xs text-slate-500">
                                <span>نسبت اشغال</span>
                                <span>{{ $ratio }}٪</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded bg-slate-100">
                                <div class="h-full bg-sky-600" style="width: {{ $ratio }}%"></div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <section class="mt-8 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4" aria-labelledby="pending-requests-heading">
        <h2 id="pending-requests-heading" class="text-base font-semibold text-slate-800">درخواست‌های در انتظار</h2>
        <p class="mt-2 text-sm text-slate-600" data-testid="pending-requests-oob">
            خارج از محدوده — Stage 3
        </p>
    </section>
</div>
