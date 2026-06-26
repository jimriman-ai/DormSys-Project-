<?php

declare(strict_types=1);

namespace App\Support\Models;

use App\Support\Traits\HasJalaliDates;
use App\Support\Traits\HasUuid;
use App\Support\Traits\RecordsActivity;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Abstract persistence model with UUID primary key, audit columns, and soft deletes.
 */
abstract class BaseModel extends Model implements Identifiable
{
    use HasJalaliDates;
    use HasUuid;
    use RecordsActivity;
    use SoftDeletes;

    /**
     * @var array<int, string>
     */
    protected array $jalaliDates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $guarded = [
        'id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'created_by' => 'string',
            'updated_by' => 'string',
            'deleted_by' => 'string',
        ];
    }

    /**
     * Return the persisted entity identifier once its UUID primary key has been assigned.
     *
     * UUID assignment occurs on the Eloquent `creating` event (see HasUuid), which may
     * precede database persistence. This matches creation-time linkage semantics such as
     * CD-012 `identity_id` assignment at Employee creation — not post-persist-only access.
     *
     * @throws \LogicException when the UUID has not yet been assigned
     */
    public function getId(): string
    {
        $key = $this->getKey();

        if ($key === null || $key === '') {
            throw new \LogicException('Model UUID not yet assigned.');
        }

        return (string) $key;
    }

    /**
     * Serialize dates as UTC ISO-8601 strings.
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format(DateTimeInterface::ATOM);
    }
}
