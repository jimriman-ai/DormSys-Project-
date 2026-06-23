<?php

declare(strict_types=1);

namespace App\Support\Traits;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

/**
 * Assigns a UUIDv7 primary key when an Eloquent model is created.
 */
trait HasUuid
{
    public static function bootHasUuid(): void
    {
        static::creating(function (Model $model): void {
            $keyName = $model->getKeyName();

            if ($model->getAttribute($keyName) === null) {
                $model->setAttribute($keyName, Uuid::uuid7()->toString());
            }
        });
    }

    public function initializeHasUuid(): void
    {
        $this->keyType = 'string';
        $this->incrementing = false;
    }
}
