<?php

declare(strict_types=1);

namespace App\Modules\Notification\Domain\Enums;

enum NotificationType: string
{
    case RequestSubmitted = 'request_submitted';
    case RequestApproved = 'request_approved';
    case RequestRejected = 'request_rejected';
    case AllocationSuccessful = 'allocation_successful';
    case LotteryWinner = 'lottery_winner';
    case VoucherIssued = 'voucher_issued';
    case ReservePromoted = 'reserve_promoted';
    case CheckInReminder = 'check_in_reminder';
}
