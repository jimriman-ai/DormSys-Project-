<?php

declare(strict_types=1);

namespace App\Modules\Employee\Infrastructure\Persistence\Models;

use App\Modules\Employee\Domain\Enums\EmployeeStatus;
use App\Modules\Employee\Domain\Exceptions\IdentityIdImmutableException;
use App\Support\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $identity_id
 * @property string $employee_code
 * @property string $first_name
 * @property string $last_name
 * @property string $national_code
 * @property string|null $department_id
 * @property Carbon $hire_date
 * @property int $base_lottery_score
 * @property EmployeeStatus $status
 */
class EmployeeModel extends BaseModel
{
    protected $table = 'employee_employees';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'employee_code',
        'first_name',
        'last_name',
        'national_code',
        'department_id',
        'hire_date',
        'base_lottery_score',
        'status',
    ];

    protected static function booted(): void
    {
        static::updating(function (EmployeeModel $model): void {
            if ($model->isDirty('identity_id') && $model->getOriginal('identity_id') !== null) {
                throw new IdentityIdImmutableException('identity_id cannot be changed after assignment.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'status' => EmployeeStatus::class,
            'hire_date' => 'date',
            'base_lottery_score' => 'integer',
        ]);
    }

    /**
     * @return BelongsTo<DepartmentModel, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(DepartmentModel::class, 'department_id');
    }

    /**
     * @return HasMany<DependentModel, $this>
     */
    public function dependents(): HasMany
    {
        return $this->hasMany(DependentModel::class, 'employee_id');
    }
}
