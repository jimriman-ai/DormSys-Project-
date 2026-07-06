<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Lottery;

use App\Modules\Employee\Domain\Entities\Employee;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Support\ValueObjects\Identity\NationalCode;
use DateTimeImmutable;
use RuntimeException;

final class LotteryTestFactory
{
    public static function createSecondEmployee(): Employee
    {
        for ($attempt = 0; $attempt < 100; $attempt++) {
            $nine = str_pad((string) random_int(100000000, 999999999), 9, '0', STR_PAD_LEFT);

            for ($check = 0; $check <= 9; $check++) {
                $candidate = $nine.(string) $check;

                if (NationalCode::isValid($candidate)) {
                    $nationalCode = NationalCode::fromString($candidate);

                    $user = createIdentityUserThroughMutation(
                        'Lottery Test User',
                        'lottery.test.'.uniqid('', true).'@example.com',
                    );

                    return createEmployeeThroughMutation(
                        identityId: IdentityUserId::fromString($user->requireId()->value),
                        employeeCode: 'EMP-LT-'.substr(uniqid('', true), -6),
                        firstName: 'Test',
                        lastName: 'Employee',
                        nationalCode: $nationalCode,
                        hireDate: new DateTimeImmutable('2024-01-01'),
                    );
                }
            }
        }

        throw new RuntimeException('Could not generate a valid national code for lottery test.');
    }
}
