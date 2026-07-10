<div>
    <x-ui.page-header
        title="کارکنان"
        description="ایجاد کارمند و دپارتمان و تخصیص/غیرفعال‌سازی دپارتمان. اعتبارسنجی نهایی در سامانه انجام می‌شود."
    />

    @if ($actionError)
        <x-ui.alert type="error" :message="$actionError" class="mb-4" />
    @endif

    @if ($successMessage)
        <x-ui.alert type="success" :message="$successMessage" class="mb-4" />
    @endif

    @if ($returnedId)
        <p class="mb-6 text-sm text-slate-700" dir="ltr" data-testid="returned-id">
            شناسه بازگردانده‌شده: {{ $returnedId }}
        </p>
    @endif

    <div class="space-y-8">
        <section class="rounded-xl border border-slate-200 bg-white p-6" aria-labelledby="create-employee-heading">
            <h2 id="create-employee-heading" class="mb-4 text-base font-semibold text-slate-900">ایجاد کارمند</h2>

            <form wire:submit="createEmployee" class="max-w-xl space-y-4">
                <x-ui.form-field label="شناسه هویت (UUID)" for="identityId" :error="$errors->first('identityId')">
                    <input
                        id="identityId"
                        type="text"
                        wire:model="identityId"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                        dir="ltr"
                    >
                </x-ui.form-field>

                <x-ui.form-field label="کد کارمند" for="employeeCode" :error="$errors->first('employeeCode')">
                    <input
                        id="employeeCode"
                        type="text"
                        wire:model="employeeCode"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                        dir="ltr"
                    >
                </x-ui.form-field>

                <x-ui.form-field label="نام" for="firstName" :error="$errors->first('firstName')">
                    <input
                        id="firstName"
                        type="text"
                        wire:model="firstName"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                    >
                </x-ui.form-field>

                <x-ui.form-field label="نام خانوادگی" for="lastName" :error="$errors->first('lastName')">
                    <input
                        id="lastName"
                        type="text"
                        wire:model="lastName"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                    >
                </x-ui.form-field>

                <x-ui.form-field label="کد ملی" for="nationalCode" :error="$errors->first('nationalCode')">
                    <input
                        id="nationalCode"
                        type="text"
                        wire:model="nationalCode"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                        dir="ltr"
                    >
                </x-ui.form-field>

                <x-ui.form-field label="تاریخ استخدام" for="hireDate" :error="$errors->first('hireDate')">
                    <input
                        id="hireDate"
                        type="date"
                        wire:model="hireDate"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                    >
                </x-ui.form-field>

                <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="createEmployee">
                    <span wire:loading.remove wire:target="createEmployee">ایجاد کارمند</span>
                    <span wire:loading wire:target="createEmployee">در حال ثبت...</span>
                </x-ui.button>
            </form>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6" aria-labelledby="create-department-heading">
            <h2 id="create-department-heading" class="mb-4 text-base font-semibold text-slate-900">ایجاد دپارتمان</h2>

            <form wire:submit="createDepartment" class="max-w-xl space-y-4">
                <x-ui.form-field label="نام دپارتمان" for="departmentName" :error="$errors->first('departmentName')">
                    <input
                        id="departmentName"
                        type="text"
                        wire:model="departmentName"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                    >
                </x-ui.form-field>

                <x-ui.form-field label="کد دپارتمان" for="departmentCode" :error="$errors->first('departmentCode')">
                    <input
                        id="departmentCode"
                        type="text"
                        wire:model="departmentCode"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                        dir="ltr"
                    >
                </x-ui.form-field>

                <x-ui.form-field label="شناسه مدیر (اختیاری)" for="managerId" :error="$errors->first('managerId')">
                    <input
                        id="managerId"
                        type="text"
                        wire:model="managerId"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                        dir="ltr"
                    >
                </x-ui.form-field>

                <x-ui.form-field label="شناسه دپارتمان والد (اختیاری)" for="parentId" :error="$errors->first('parentId')">
                    <input
                        id="parentId"
                        type="text"
                        wire:model="parentId"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                        dir="ltr"
                    >
                </x-ui.form-field>

                <x-ui.form-field label="اولویت قرعه‌کشی" for="lotteryPriority" :error="$errors->first('lotteryPriority')">
                    <input
                        id="lotteryPriority"
                        type="number"
                        wire:model="lotteryPriority"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                        dir="ltr"
                    >
                </x-ui.form-field>

                <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="createDepartment">
                    <span wire:loading.remove wire:target="createDepartment">ایجاد دپارتمان</span>
                    <span wire:loading wire:target="createDepartment">در حال ثبت...</span>
                </x-ui.button>
            </form>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6" aria-labelledby="assign-department-heading">
            <h2 id="assign-department-heading" class="mb-4 text-base font-semibold text-slate-900">تخصیص دپارتمان به کارمند</h2>

            <form wire:submit="assignDepartment" class="max-w-xl space-y-4">
                <x-ui.form-field label="شناسه کارمند (UUID)" for="assignEmployeeId" :error="$errors->first('assignEmployeeId')">
                    <input
                        id="assignEmployeeId"
                        type="text"
                        wire:model="assignEmployeeId"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                        dir="ltr"
                    >
                </x-ui.form-field>

                <x-ui.form-field label="شناسه دپارتمان (UUID)" for="assignDepartmentId" :error="$errors->first('assignDepartmentId')">
                    <input
                        id="assignDepartmentId"
                        type="text"
                        wire:model="assignDepartmentId"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                        dir="ltr"
                    >
                </x-ui.form-field>

                <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="assignDepartment">
                    <span wire:loading.remove wire:target="assignDepartment">تخصیص دپارتمان</span>
                    <span wire:loading wire:target="assignDepartment">در حال ثبت...</span>
                </x-ui.button>
            </form>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6" aria-labelledby="deactivate-department-heading">
            <h2 id="deactivate-department-heading" class="mb-4 text-base font-semibold text-slate-900">غیرفعال‌سازی دپارتمان</h2>

            <form wire:submit="deactivateDepartment" class="max-w-xl space-y-4">
                <x-ui.form-field label="شناسه دپارتمان (UUID)" for="deactivateDepartmentId" :error="$errors->first('deactivateDepartmentId')">
                    <input
                        id="deactivateDepartmentId"
                        type="text"
                        wire:model="deactivateDepartmentId"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                        dir="ltr"
                    >
                </x-ui.form-field>

                <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="deactivateDepartment">
                    <span wire:loading.remove wire:target="deactivateDepartment">غیرفعال‌سازی دپارتمان</span>
                    <span wire:loading wire:target="deactivateDepartment">در حال ثبت...</span>
                </x-ui.button>
            </form>
        </section>
    </div>
</div>
