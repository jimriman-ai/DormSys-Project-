<?php

declare(strict_types=1);

namespace App\Modules\Request\Infrastructure\Persistence\Models;

use App\Modules\Employee\Infrastructure\Persistence\Models\DependentModel;
use App\Modules\Request\Domain\Enums\DependentRelationship;
use App\Modules\Request\Domain\Exceptions\AppendOnlyViolationException;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Immutable dependent snapshot rows for FamilyDirect requests (CD-009).
 *
 * @property string $id
 * @property string $request_id
 * @property string|null $source_dependent_id
 * @property string $first_name
 * @property string $last_name
 * @property DependentRelationship $relationship
 * @property string|null $national_code
 * @property Carbon $captured_at
 * @property Carbon $created_at
 */
class RequestDependentSnapshotModel extends Model
{
    use HasUuid;

    public $timestamps = false;

    protected $table = 'request_dependent_snapshots';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'request_id',
        'source_dependent_id',
        'first_name',
        'last_name',
        'relationship',
        'national_code',
        'captured_at',
        'created_at',
    ];

    protected static function booted(): void
    {
        static::updating(static function (): void {
            throw new AppendOnlyViolationException('Dependent snapshot records are append-only.');
        });

        static::deleting(static function (): void {
            throw new AppendOnlyViolationException('Dependent snapshot records are append-only.');
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'relationship' => DependentRelationship::class,
            'captured_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    /**
     * request_id → requests.id (physical FK present; Eloquent relation).
     *
     * @return BelongsTo<RequestModel, $this>
     */
    public function request(): BelongsTo
    {
        return $this->belongsTo(RequestModel::class, 'request_id');
    }

    /**
     * Value-ref (AP-04): source_dependent_id → employee_dependents.id — Eloquent only, no physical FK.
     *
     * @return BelongsTo<DependentModel, $this>
     */
    public function sourceDependent(): BelongsTo
    {
        return $this->belongsTo(DependentModel::class, 'source_dependent_id');
    }
}
