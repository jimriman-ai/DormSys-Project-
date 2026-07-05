<?php

declare(strict_types=1);

use App\Modules\Lottery\Application\Contracts\LotteryProgramRepositoryContract;
use App\Modules\Lottery\Domain\Models\LotteryProgram;
use App\Modules\Lottery\Domain\States\DraftState;
use App\Modules\Lottery\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Lottery\Domain\ValueObjects\LotteryProgramId;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('persists a draft lottery program with draft status', function (): void {
    $dormitoryId = DormitorySiteId::fromString(UuidGenerator::uuid7());

    $program = LotteryProgram::createDraft(
        title: 'Summer 2026 Draw',
        dormitoryId: $dormitoryId,
        capacity: 50,
        registrationStartsAt: new DateTimeImmutable('2026-07-01 00:00:00', new DateTimeZone('UTC')),
        registrationEndsAt: new DateTimeImmutable('2026-07-15 23:59:59', new DateTimeZone('UTC')),
    );

    $saved = app(LotteryProgramRepositoryContract::class)->save($program);

    expect($saved->id)->toBeInstanceOf(LotteryProgramId::class);
    expect($saved->isDraft())->toBeTrue();
    expect($saved->status)->toBe(DraftState::$name);

    $reloaded = app(LotteryProgramRepositoryContract::class)->findById($saved->requireId());
    if (! $reloaded instanceof LotteryProgram) {
        throw new UnexpectedValueException('Expected reloaded lottery program.');
    }

    expect($reloaded->title)->toBe('Summer 2026 Draw');
    expect($reloaded->capacity)->toBe(50);
});

it('runs lottery module migrations', function (): void {
    expect(Schema::hasTable('lottery_programs'))->toBeTrue();
    expect(Schema::hasTable('lottery_registrations'))->toBeTrue();
    expect(Schema::hasTable('lottery_results'))->toBeTrue();
    expect(Schema::hasTable('lottery_eligible_snapshots'))->toBeTrue();
});
