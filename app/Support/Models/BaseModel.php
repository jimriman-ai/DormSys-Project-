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

    public function getId(): string
    {
        return (string) $this->getKey();
    }

    /**
     * Serialize dates as UTC ISO-8601 strings.
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format(DateTimeInterface::ATOM);
    }
}
