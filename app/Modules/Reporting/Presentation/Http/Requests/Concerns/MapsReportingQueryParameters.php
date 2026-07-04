<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Presentation\Http\Requests\Concerns;

use App\Modules\Reporting\Domain\Enums\WindowGranularity;
use DateTimeImmutable;
use DateTimeZone;

trait MapsReportingQueryParameters
{
    protected function queryBoolean(string $key, bool $default = false): bool
    {
        if (! $this->has($key)) {
            return $default;
        }

        return filter_var($this->query($key), FILTER_VALIDATE_BOOLEAN);
    }

    protected function queryInt(string $key, int $default): int
    {
        if (! $this->has($key)) {
            return $default;
        }

        return (int) $this->query($key);
    }

    /**
     * @return list<string>|null
     */
    protected function queryStringList(?string $key): ?array
    {
        if ($key === null || ! $this->has($key)) {
            return null;
        }

        $value = $this->query($key);

        if (is_array($value)) {
            /** @var list<string> $items */
            $items = array_values(array_filter(array_map('strval', $value), static fn (string $item): bool => $item !== ''));

            return $items === [] ? null : $items;
        }

        if (! is_string($value) || $value === '') {
            return null;
        }

        /** @var list<string> $items */
        $items = array_values(array_filter(array_map('trim', explode(',', $value)), static fn (string $item): bool => $item !== ''));

        return $items === [] ? null : $items;
    }

    protected function queryDateTime(string $key): ?DateTimeImmutable
    {
        if (! $this->has($key)) {
            return null;
        }

        $value = $this->query($key);

        if (! is_string($value) || $value === '') {
            return null;
        }

        return new DateTimeImmutable($value, new DateTimeZone('UTC'));
    }

    protected function requiredDateTime(string $key): DateTimeImmutable
    {
        $value = $this->query($key);

        if (! is_string($value) || $value === '') {
            throw new \InvalidArgumentException("{$key} is required.");
        }

        return new DateTimeImmutable($value, new DateTimeZone('UTC'));
    }

    protected function queryGranularity(string $key = 'granularity'): WindowGranularity
    {
        $value = $this->query($key, WindowGranularity::Day->value);

        if (! is_string($value) || $value === '') {
            return WindowGranularity::Day;
        }

        return WindowGranularity::from($value);
    }
}
