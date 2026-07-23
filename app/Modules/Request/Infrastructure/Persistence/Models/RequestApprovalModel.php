<?php

declare(strict_types=1);

namespace App\Modules\Request\Infrastructure\Persistence\Models;

use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use App\Modules\Request\Domain\Enums\ApprovalDecision;
use App\Modules\Request\Domain\Enums\ApprovalStage;
use App\Modules\Request\Domain\Exceptions\AppendOnlyViolationException;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Append-only approval history — no updates or deletes (R-08).
 *
 * @property string $id
 * @property string $request_id
 * @property ApprovalStage $stage
 * @property ApprovalDecision $decision
 * @property string $approver_id
 * @property string|null $reason
 * @property Carbon $decided_at
 * @property Carbon $created_at
 */
class RequestApprovalModel extends Model
{
    use HasUuid;

    public $timestamps = false;

    protected $table = 'request_approvals';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'request_id',
        'stage',
        'decision',
        'approver_id',
        'reason',
        'decided_at',
        'created_at',
    ];

    protected static function booted(): void
    {
        static::updating(static function (): void {
            throw new AppendOnlyViolationException('Request approval records are append-only.');
        });

        static::deleting(static function (): void {
            throw new AppendOnlyViolationException('Request approval records are append-only.');
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stage' => ApprovalStage::class,
            'decision' => ApprovalDecision::class,
            'decided_at' => 'datetime',
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
     * Value-ref (AP-04): approver_id → identity_users.id — Eloquent only, no physical FK.
     *
     * @return BelongsTo<UserModel, $this>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'approver_id');
    }
}
