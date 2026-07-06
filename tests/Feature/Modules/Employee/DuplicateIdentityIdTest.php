<?php

declare(strict_types=1);

use App\Modules\Employee\Domain\Exceptions\DuplicateIdentityIdException;
use App\Modules\Employee\Domain\ValueObjects\IdentityUserId;
use App\Support\ValueObjects\Identity\NationalCode;

it('rejects a second employee for the same identity_id', function (): void {
    $user = createIdentityUserThroughMutation('Duplicate Identity User', 'dup.identity@example.com');
    $identityId = IdentityUserId::fromString($user->requireId()->value);

    createEmployeeThroughMutation(
        identityId: $identityId,
        employeeCode: 'EMP-DUP-1',
        firstName: 'First',
        lastName: 'Employee',
        nationalCode: NationalCode::fromString('0499370899'),
        hireDate: new DateTimeImmutable('2024-01-01'),
    );

    expect(fn () => createEmployeeThroughMutation(
        identityId: $identityId,
        employeeCode: 'EMP-DUP-2',
        firstName: 'Second',
        lastName: 'Employee',
        nationalCode: NationalCode::fromString('0499370899'),
        hireDate: new DateTimeImmutable('2024-02-01'),
    ))->toThrow(DuplicateIdentityIdException::class);
});
