<?php

declare(strict_types=1);

namespace App\Modules\Voucher\Infrastructure\Persistence\Models;

use App\Modules\Voucher\Domain\Enums\EligibilityOutcome;
use App\Support\Models\BaseModel;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property string $id
 * @property string $trigger_id
 * @property string $correlation_id
 * @property string $employee_id
 * @property string|null $dormitory_id
 * @property string|null $request_id
 * @property EligibilityOutcome $outcome
 * @property list<string> $reason_codes
 * @property string $rationale
 * @property Carbon $evaluated_at
 */
class VoucherEligibilityOutcomeModel extends BaseModel
{
    protected $table = 'voucher_eligibility_outcomes';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'trigger_id',
        'correlation_id',
        'employee_id',
        'dormitory_id',
        'request_id',
        'outcome',
        'reason_codes',
        'rationale',
        'evaluated_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'outcome' => EligibilityOutcome::class,
            'reason_codes' => 'array',
            'evaluated_at' => 'datetime',
        ]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('voucher_eligibility')
            ->logOnly([
                'trigger_id',
                'correlation_id',
                'employee_id',
                'outcome',
                'reason_codes',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName): string => match ($eventName) {
                'created' => 'voucher eligibility evaluated',
                'updated' => 'voucher eligibility updated',
                default => $eventName,
            });
    }
}
