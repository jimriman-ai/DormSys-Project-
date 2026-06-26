<?php

declare(strict_types=1);

namespace App\Modules\Employee\Infrastructure\Persistence\Models;

use App\Modules\Employee\Domain\Enums\DepartmentStatus;
use App\Support\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $name
 * @property string $code
 * @property string|null $manager_id
 * @property string|null $parent_id
 * @property int $lottery_priority
 * @property DepartmentStatus $status
 */
class DepartmentModel extends BaseModel
{
    protected $table = 'employee_departments';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'code',
        'manager_id',
        'parent_id',
        'lottery_priority',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'status' => DepartmentStatus::class,
            'lottery_priority' => 'integer',
        ]);
    }

    /**
     * @return HasMany<EmployeeModel, $this>
     */
    public function employees(): HasMany
    {
        return $this->hasMany(EmployeeModel::class, 'department_id');
    }
}
