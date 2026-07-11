<?php

declare(strict_types=1);

namespace App\Modules\Employee\Infrastructure\Persistence\Models;

use App\Modules\Employee\Domain\Enums\DependentRelationship;
use App\Support\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $employee_id
 * @property string $first_name
 * @property string $last_name
 * @property DependentRelationship $relationship
 * @property int|null $age
 * @property string|null $national_code
 */
class DependentModel extends BaseModel
{
    protected $table = 'employee_dependents';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'first_name',
        'last_name',
        'relationship',
        'age',
        'national_code',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'relationship' => DependentRelationship::class,
            'age' => 'integer',
        ]);
    }

    /**
     * @return BelongsTo<EmployeeModel, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(EmployeeModel::class, 'employee_id');
    }
}
