<div>
    <h1 class="text-xl font-semibold text-slate-900">داشبورد مدیر واحد خوابگاه</h1>

    <section class="mt-6" aria-labelledby="unit-room-list-heading">
        <h2 id="unit-room-list-heading" class="text-base font-semibold text-slate-800">اتاق‌های اختصاص‌یافته</h2>

        @if (count($rooms) === 0)
            <p class="mt-3 text-sm text-slate-600" data-testid="unit-manager-empty">
                اتاقی به شما اختصاص داده نشده است.
            </p>
        @else
            <ul class="mt-4 space-y-4">
                @foreach ($rooms as $room)
                    @php
                        $ratio = $room['bed_total'] > 0
                            ? (int) round(($room['bed_occupied'] / $room['bed_total']) * 100)
                            : 0;
                    @endphp
                    <li
                        class="rounded-xl border border-slate-200 bg-white p-4"
                        data-testid="unit-room-card"
                        data-room-id="{{ $room['id'] }}"
                    >
                        <h3 class="text-base font-semibold text-slate-900">{{ $room['room_label'] }}</h3>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ $room['dormitory_name'] }} · {{ $room['building_name'] }} · طبقه {{ $room['floor_label'] }}
                        </p>

                        <dl class="mt-3 grid grid-cols-2 gap-2 text-sm text-slate-700 sm:grid-cols-4">
                            <div>
                                <dt class="text-slate-500">تخت کل</dt>
                                <dd data-testid="bed-total">{{ $room['bed_total'] }}</dd>
                            </div>
                            <div>
                                <dt class="text-slate-500">اشغال‌شده</dt>
                                <dd data-testid="bed-occupied">{{ $room['bed_occupied'] }}</dd>
                            </div>
                            <div>
                                <dt class="text-slate-500">رزرو</dt>
                                <dd data-testid="bed-reserved">{{ $room['bed_reserved'] }}</dd>
                            </div>
                            <div>
                                <dt class="text-slate-500">خالی</dt>
                                <dd data-testid="bed-vacant">{{ $room['bed_vacant'] }}</dd>
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

    <section class="mt-8 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4" aria-labelledby="residents-heading">
        <h2 id="residents-heading" class="text-base font-semibold text-slate-800">ساکنین</h2>
        <p class="mt-2 text-sm text-slate-600" data-testid="residents-oob">
            خارج از محدوده — Stage 3
        </p>
    </section>
</div>
