<?php

declare(strict_types=1);

namespace App\Modules\Request\Infrastructure\Persistence\Models;

use App\Modules\Request\Domain\Exceptions\AppendOnlyViolationException;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $request_id
 * @property string $employee_id
 * @property bool $is_leader
 * @property Carbon $created_at
 */
class RequestMemberModel extends Model
{
    use HasUuid;

    public $timestamps = false;

    protected $table = 'request_members';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'request_id',
        'employee_id',
        'is_leader',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_leader' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(static function (): void {
            throw new AppendOnlyViolationException('Request member records are append-only.');
        });

        static::deleting(static function (): void {
            throw new AppendOnlyViolationException('Request member records are append-only.');
        });
    }
}
