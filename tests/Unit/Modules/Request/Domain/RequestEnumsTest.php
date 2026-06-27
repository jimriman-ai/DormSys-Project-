<?php

declare(strict_types=1);

use App\Modules\Request\Domain\Enums\ApprovalDecision;
use App\Modules\Request\Domain\Enums\ApprovalStage;
use App\Modules\Request\Domain\Enums\RequestType;

it('maps request types to persistence values', function (): void {
    expect(RequestType::Personal->value)->toBe('personal');
    expect(RequestType::FamilyDirect->value)->toBe('family_direct');
    expect(RequestType::Mission->value)->toBe('mission');
    expect(RequestType::LotteryRegistration->value)->toBe('lottery_registration');
});

it('maps approval stages to the four-stage chain', function (): void {
    expect(ApprovalStage::cases())->toHaveCount(4);
    expect(ApprovalStage::DepartmentManager->value)->toBe('department_manager');
    expect(ApprovalStage::HR->value)->toBe('hr');
    expect(ApprovalStage::DormitoryManager->value)->toBe('dormitory_manager');
    expect(ApprovalStage::DormitoryUnit->value)->toBe('dormitory_unit');
});

it('maps approval decisions including pending', function (): void {
    expect(ApprovalDecision::Approved->value)->toBe('approved');
    expect(ApprovalDecision::Rejected->value)->toBe('rejected');
    expect(ApprovalDecision::Pending->value)->toBe('pending');
});

it('round-trips enum values from strings', function (string $class, string $value): void {
    expect($class::from($value)->value)->toBe($value);
})->with([
    'request type personal' => [RequestType::class, 'personal'],
    'approval stage hr' => [ApprovalStage::class, 'hr'],
    'approval decision pending' => [ApprovalDecision::class, 'pending'],
]);
