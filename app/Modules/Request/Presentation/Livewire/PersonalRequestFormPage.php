<?php

declare(strict_types=1);

namespace App\Modules\Request\Presentation\Livewire;

use App\Modules\Request\Application\Services\CreatePersonalRequestAction;
use App\Modules\Request\Application\Services\RequestPrincipalEmployeeResolver;
use App\Modules\Request\Application\Services\SubmitRequestAction;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use App\Support\Presentation\Concerns\HandlesUiMutationFeedback;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Contracts\Auth\Authenticatable;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
final class PersonalRequestFormPage extends Component
{
    use HandlesUiMutationFeedback;

    public string $dormitory_site_id = '';

    public string $check_in_date = '';

    public string $check_out_date = '';

    public bool $submitting = false;

    public function submit(
        CreatePersonalRequestAction $createPersonalRequest,
        SubmitRequestAction $submitRequest,
        RequestPrincipalEmployeeResolver $principalEmployee,
    ): void {
        $this->resetActionFeedback();
        $this->submitting = true;
        $this->bindMutationPrincipalFromAuth();

        $validated = $this->validate([
            'dormitory_site_id' => ['required', 'uuid'],
            'check_in_date' => ['required', 'date_format:Y-m-d'],
            'check_out_date' => ['required', 'date_format:Y-m-d', 'after:check_in_date'],
        ]);

        try {
            $created = $createPersonalRequest->execute(
                employeeId: EmployeeReferenceId::fromString($principalEmployee->requireEmployeeId()),
                dormitoryId: DormitorySiteId::fromString($validated['dormitory_site_id']),
                checkInDate: new DateTimeImmutable($validated['check_in_date'], new DateTimeZone('UTC')),
                checkOutDate: new DateTimeImmutable($validated['check_out_date'], new DateTimeZone('UTC')),
            );

            $submitRequest->execute($created->requireId());

            $this->flashSuccess('درخواست با موفقیت ثبت و ارسال شد.');
            $this->redirectRoute('employee.requests.index');
        } catch (\Throwable $exception) {
            $this->captureMutationFailure($exception);
        } finally {
            $this->submitting = false;
        }
    }

    public function render(): string
    {
        return <<<'HTML'
        <div>
            <x-ui.page-header title="ثبت درخواست شخصی" description="اطلاعات درخواست را وارد کنید. پس از ثبت، درخواست برای بررسی ارسال می‌شود." />

            @if ($actionError)
                <x-ui.alert type="error" :message="$actionError" class="mb-4" />
            @endif

            <form wire:submit="submit" class="max-w-xl space-y-4 rounded-xl border border-slate-200 bg-white p-6">
                <x-ui.form-field label="خوابگاه" for="dormitory_site_id" :error="$errors->first('dormitory_site_id')">
                    <select
                        id="dormitory_site_id"
                        wire:model="dormitory_site_id"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                        dir="ltr"
                    >
                        <option value="">انتخاب کنید</option>
                    </select>
                </x-ui.form-field>

                <x-ui.form-field label="تاریخ ورود" for="check_in_date" :error="$errors->first('check_in_date')">
                    <input
                        id="check_in_date"
                        type="date"
                        wire:model="check_in_date"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                    >
                </x-ui.form-field>

                <x-ui.form-field label="تاریخ خروج" for="check_out_date" :error="$errors->first('check_out_date')">
                    <input
                        id="check_out_date"
                        type="date"
                        wire:model="check_out_date"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                    >
                </x-ui.form-field>

                <div class="flex items-center gap-3 pt-2">
                    <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="submit">
                        <span wire:loading.remove wire:target="submit">ثبت و ارسال درخواست</span>
                        <span wire:loading wire:target="submit">در حال ثبت...</span>
                    </x-ui.button>

                    <a href="{{ route('employee.requests.index') }}" class="text-sm text-slate-600 hover:text-slate-900" wire:navigate>
                        بازگشت
                    </a>
                </div>
            </form>
        </div>
        HTML;
    }

    private function bindMutationPrincipalFromAuth(): void
    {
        $user = auth('identity')->user();

        if (! $user instanceof Authenticatable) {
            abort(401);
        }

        $principalId = (string) $user->getAuthIdentifier();

        if ($principalId === '') {
            abort(401);
        }

        request()->attributes->set('audit_principal_user_id', $principalId);
    }
}
