<?php

declare(strict_types=1);

namespace App\Modules\Audit\Domain\Enums;

enum AuditEventType: string
{
    case RequestSubmitted = 'request.submitted';
    case RequestStateChanged = 'request.state_changed';
    case RequestApproved = 'request.approved';
    case RequestRejected = 'request.rejected';
    case LotteryProgramCreated = 'lottery.program_created';
    case LotteryExecuted = 'lottery.executed';
    case LotteryReservePromoted = 'lottery.reserve_promoted';
    case AllocationCreated = 'allocation.created';
    case AllocationModified = 'allocation.modified';
    case AllocationCancelled = 'allocation.cancelled';
    case CheckInRecorded = 'check_in.recorded';
    case CheckOutRecorded = 'check_out.recorded';
    case VoucherIssued = 'voucher.issued';
    case VoucherStateChanged = 'voucher.state_changed';
    case IdentityRoleChanged = 'identity.role_changed';
    case IdentityPermissionChanged = 'identity.permission_changed';
    case IdentityUserCreated = 'identity.user_created';
    case IdentityUserDeactivated = 'identity.user_deactivated';
    case DormitoryRoomStatusChanged = 'dormitory.room_status_changed';
}
