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

    public const string IDENTITY_USER_CREATE = 'identity.user.create';

    public const string IDENTITY_USER_DEACTIVATE = 'identity.user.deactivate';

    public const string IDENTITY_ROLE_ASSIGN = 'identity.role.assign';

    public const string IDENTITY_ROLE_REVOKE = 'identity.role.revoke';

    public const string IDENTITY_ROLE_MANAGE = 'identity.role.manage';

    public const string EMPLOYEE_CREATE = 'employee.create';

    public const string EMPLOYEE_DEPARTMENT_CREATE = 'employee.department.create';

    public const string EMPLOYEE_DEPARTMENT_DEACTIVATE = 'employee.department.deactivate';

    public const string EMPLOYEE_DEPARTMENT_ASSIGN = 'employee.department.assign';

    public const string EMPLOYEE_DEPENDENT_ADD = 'employee.dependent.add';

    public const string EMPLOYEE_DEPENDENT_UPDATE = 'employee.dependent.update';

    public const string LOTTERY_PROGRAM_CREATE = 'lottery.program.create';

    public const string LOTTERY_PROGRAM_OPEN_REGISTRATION = 'lottery.program.open_registration';

    public const string LOTTERY_PROGRAM_CLOSE_REGISTRATION = 'lottery.program.close_registration';

    public const string LOTTERY_PROGRAM_CANCEL = 'lottery.program.cancel';

    public const string LOTTERY_PROGRAM_LOCK = 'lottery.program.lock';

    public const string LOTTERY_PROGRAM_DRAW = 'lottery.program.draw';

    public const string LOTTERY_ENROLL_OWN = 'lottery.enroll.own';

    public const string ALLOCATION_CREATE = 'allocation.create';

    public const string ALLOCATION_CREATE_FROM_REQUEST = 'allocation.create.from_request';

    public const string ALLOCATION_RELEASE = 'allocation.release';

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
            self::IDENTITY_USER_CREATE,
            self::IDENTITY_USER_DEACTIVATE,
            self::IDENTITY_ROLE_ASSIGN,
            self::IDENTITY_ROLE_REVOKE,
            self::IDENTITY_ROLE_MANAGE,
            self::EMPLOYEE_CREATE,
            self::EMPLOYEE_DEPARTMENT_CREATE,
            self::EMPLOYEE_DEPARTMENT_DEACTIVATE,
            self::EMPLOYEE_DEPARTMENT_ASSIGN,
            self::EMPLOYEE_DEPENDENT_ADD,
            self::EMPLOYEE_DEPENDENT_UPDATE,
            self::LOTTERY_PROGRAM_CREATE,
            self::LOTTERY_PROGRAM_OPEN_REGISTRATION,
            self::LOTTERY_PROGRAM_CLOSE_REGISTRATION,
            self::LOTTERY_PROGRAM_CANCEL,
            self::LOTTERY_PROGRAM_LOCK,
            self::LOTTERY_PROGRAM_DRAW,
            self::LOTTERY_ENROLL_OWN,
            self::ALLOCATION_CREATE,
            self::ALLOCATION_CREATE_FROM_REQUEST,
            self::ALLOCATION_RELEASE,
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
