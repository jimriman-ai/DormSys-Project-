<?php

declare(strict_types=1);

namespace App\Application\Mutation\Registry;

use App\Support\Exceptions\ValidationException;

final class MutationCapabilityCatalog
{
    public const string REQUEST_SUBMIT_OWN = 'request.submit.own';

    public const string REQUEST_CANCEL_OWN = 'request.cancel.own';

    public const string REQUEST_APPROVE = 'request.approve';

    public const string REQUEST_REJECT = 'request.reject';

    public const string CHECKIN_CREATE = 'checkin.create';

    public const string CHECKIN_OPERATE = 'checkin.operate';

    public const string CHECKIN_CLOSE = 'checkin.close';

    /**
     * @return list<string>
     */
    public static function registeredKeys(): array
    {
        return [
            self::REQUEST_SUBMIT_OWN,
            self::REQUEST_CANCEL_OWN,
            self::REQUEST_APPROVE,
            self::REQUEST_REJECT,
            self::CHECKIN_CREATE,
            self::CHECKIN_OPERATE,
            self::CHECKIN_CLOSE,
        ];
    }

    public static function assertValidKey(string $capabilityKey): void
    {
        if ($capabilityKey === '') {
            throw new ValidationException('Mutation capability key must not be empty.');
        }
    }

    public static function assertRegistered(string $capabilityKey): void
    {
        self::assertValidKey($capabilityKey);

        if (! in_array($capabilityKey, self::registeredKeys(), true)) {
            throw new ValidationException("Unknown mutation capability [{$capabilityKey}].");
        }
    }
}
