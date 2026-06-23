<?php

declare(strict_types=1);

namespace App\Support\Traits;

use App\Support\ValueObjects\Jalali\JalaliDate;
use Carbon\CarbonInterface;
use DateTimeInterface;

/**
 * Exposes Jalali date value objects for configured Eloquent date attributes.
 */
trait HasJalaliDates
{
    /**
     * @return array<int, string>
     */
    protected function jalaliDateAttributes(): array
    {
        if (! property_exists($this, 'jalaliDates')) {
            return ['created_at', 'updated_at'];
        }

        /** @var array<int, string> $attributes */
        $attributes = $this->jalaliDates;

        return $attributes;
    }

    /**
     * Convert a model date attribute to an immutable Jalali value object.
     */
    public function toJalali(string $attribute): ?JalaliDate
    {
        if (! in_array($attribute, $this->jalaliDateAttributes(), true)) {
            return null;
        }

        $value = $this->getAttribute($attribute);

        if ($value === null) {
            return null;
        }

        if ($value instanceof JalaliDate) {
            return $value;
        }

        if ($value instanceof CarbonInterface) {
            return JalaliDate::fromDateTime($value);
        }

        if ($value instanceof DateTimeInterface) {
            return JalaliDate::fromDateTime($value);
        }

        return JalaliDate::fromDateTime((string) $value);
    }
}
