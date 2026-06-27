<?php

declare(strict_types=1);

use App\Modules\Request\Domain\Exceptions\InvalidGroupRequestException;
use App\Modules\Request\Domain\Services\MissionGroupValidator;

it('accepts a valid three-member mission group', function (): void {
    app(MissionGroupValidator::class)->validate([
        ['employeeId' => '11111111-1111-4111-8111-111111111111', 'isLeader' => true],
        ['employeeId' => '22222222-2222-4222-8222-222222222222', 'isLeader' => false],
        ['employeeId' => '33333333-3333-4333-8333-333333333333', 'isLeader' => false],
    ]);

    expect(true)->toBeTrue();
});

it('rejects mission groups outside the 2-20 member range', function (int $count): void {
    $members = [];

    for ($i = 0; $i < $count; $i++) {
        $members[] = [
            'employeeId' => sprintf('aaaaaaaa-aaaa-4aaa-8aaa-%012d', $i),
            'isLeader' => $i === 0,
        ];
    }

    expect(fn () => app(MissionGroupValidator::class)->validate($members))
        ->toThrow(InvalidGroupRequestException::class);
})->with([1, 21]);

it('rejects mission groups without exactly one leader', function (): void {
    expect(fn () => app(MissionGroupValidator::class)->validate([
        ['employeeId' => '11111111-1111-4111-8111-111111111111', 'isLeader' => false],
        ['employeeId' => '22222222-2222-4222-8222-222222222222', 'isLeader' => false],
    ]))->toThrow(InvalidGroupRequestException::class);
});
