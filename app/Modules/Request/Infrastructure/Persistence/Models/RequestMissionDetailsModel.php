<?php

declare(strict_types=1);

namespace App\Modules\Request\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $request_id
 * @property string|null $mission_document_url
 * @property string $description
 * @property Carbon $created_at
 */
class RequestMissionDetailsModel extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $table = 'request_mission_details';

    protected $primaryKey = 'request_id';

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'request_id',
        'mission_document_url',
        'description',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * request_id → requests.id (physical FK present; Eloquent relation). PK = request_id.
     *
     * @return BelongsTo<RequestModel, $this>
     */
    public function request(): BelongsTo
    {
        return $this->belongsTo(RequestModel::class, 'request_id');
    }
}
