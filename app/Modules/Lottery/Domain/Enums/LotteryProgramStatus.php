<?php

declare(strict_types=1);

namespace App\Modules\Lottery\Domain\Enums;

enum LotteryProgramStatus: string
{
    case Draft = 'draft';
    case WaitingApproval = 'waiting_approval';
    case Approved = 'approved';
    case RegistrationOpen = 'registration_open';
    case RegistrationClosed = 'registration_closed';
    case Locked = 'locked';
    case Drawn = 'drawn';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function isTerminal(): bool
    {
        return $this === self::Completed || $this === self::Cancelled;
    }

    public function allowsEnrollment(): bool
    {
        return $this === self::RegistrationOpen;
    }
}
