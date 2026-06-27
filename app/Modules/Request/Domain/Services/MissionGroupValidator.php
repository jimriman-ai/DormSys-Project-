<?php

declare(strict_types=1);

namespace App\Modules\Request\Domain\Services;

use App\Modules\Request\Domain\Exceptions\InvalidGroupRequestException;

final class MissionGroupValidator
{
    private const int MIN_MEMBERS = 2;

    private const int MAX_MEMBERS = 20;

    /**
     * @param  list<array{employeeId: string, isLeader: bool}>  $members
     */
    public function validate(array $members): void
    {
        $count = count($members);

        if ($count < self::MIN_MEMBERS || $count > self::MAX_MEMBERS) {
            throw new InvalidGroupRequestException(
                'Mission requests require between 2 and 20 members.',
            );
        }

        $leaderCount = 0;
        $employeeIds = [];

        foreach ($members as $member) {
            if ($member['isLeader']) {
                $leaderCount++;
            }

            $employeeIds[] = $member['employeeId'];
        }

        if ($leaderCount !== 1) {
            throw new InvalidGroupRequestException(
                'Mission requests require exactly one group leader.',
            );
        }

        if (count($employeeIds) !== count(array_unique($employeeIds))) {
            throw new InvalidGroupRequestException(
                'Mission members must be unique.',
            );
        }
    }
}
