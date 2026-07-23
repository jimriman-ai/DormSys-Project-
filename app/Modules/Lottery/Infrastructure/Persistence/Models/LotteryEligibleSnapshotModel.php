<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Infrastructure\Persistence\Models;

use App\Modules\Lottery\Domain\Exceptions\LotteryValidationException;
use App\Support\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $program_id
 * @property array<string, mixed> $payload
 * @property string $random_seed
 * @property array<string, mixed> $scoring_config
 * @property string|null $scoring_config_version
 */
class LotteryEligibleSnapshotModel extends BaseModel
{
    protected $table = 'lottery_eligible_snapshots';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'program_id',
        'payload',
        'random_seed',
        'scoring_config',
        'scoring_config_version',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'payload' => 'array',
            'scoring_config' => 'array',
        ]);
    }

    protected static function booted(): void
    {
        static::updating(static function (): void {
            throw new LotteryValidationException('Eligible snapshot is immutable after capture.');
        });

        static::deleting(static function (): void {
            throw new LotteryValidationException('Eligible snapshot is immutable after capture.');
        });
    }

    /**
     * program_id → lottery_programs.id (physical FK present; Eloquent relation).
     *
     * @return BelongsTo<LotteryProgramModel, $this>
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(LotteryProgramModel::class, 'program_id');
    }
}
