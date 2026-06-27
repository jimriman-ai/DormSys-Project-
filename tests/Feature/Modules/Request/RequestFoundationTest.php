<?php

declare(strict_types=1);

use App\Modules\Request\Application\Contracts\RequestRepositoryContract;
use App\Modules\Request\Application\Services\RequestCodeGenerator;
use App\Modules\Request\Domain\Entities\Request;
use App\Modules\Request\Domain\Enums\RequestType;
use App\Modules\Request\Domain\States\DraftState;
use App\Modules\Request\Domain\ValueObjects\DormitorySiteId;
use App\Modules\Request\Domain\ValueObjects\EmployeeReferenceId;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use App\Shared\Infrastructure\Uuid\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('persists a draft request with draft status', function (): void {
    $code = app(RequestCodeGenerator::class)->generate();
    $employeeId = UuidGenerator::uuid7();
    $dormitoryId = UuidGenerator::uuid7();

    $request = Request::createDraft(
        code: $code,
        employeeId: EmployeeReferenceId::fromString($employeeId),
        dormitoryId: DormitorySiteId::fromString($dormitoryId),
        type: RequestType::Personal,
        checkInDate: new DateTimeImmutable('2026-07-01', new DateTimeZone('UTC')),
        checkOutDate: new DateTimeImmutable('2026-07-31', new DateTimeZone('UTC')),
    );

    $saved = app(RequestRepositoryContract::class)->save($request);

    expect($saved->id)->toBeInstanceOf(RequestId::class);
    expect($saved->isDraft())->toBeTrue();
    expect($saved->status)->toBe(DraftState::$name);

    $reloaded = app(RequestRepositoryContract::class)->findById($saved->requireId());

    expect($reloaded)->not->toBeNull();
    expect($reloaded->code->equals($code))->toBeTrue();
    expect($reloaded->status)->toBe(DraftState::$name);
});

it('generates sequential request codes for the same utc day', function (): void {
    $generator = app(RequestCodeGenerator::class);
    $at = new DateTimeImmutable('2026-06-23 12:00:00', new DateTimeZone('UTC'));
    $employeeId = EmployeeReferenceId::fromString(UuidGenerator::uuid7());
    $dormitoryId = DormitorySiteId::fromString(UuidGenerator::uuid7());
    $repository = app(RequestRepositoryContract::class);

    $firstCode = $generator->generate($at);
    $repository->save(Request::createDraft(
        code: $firstCode,
        employeeId: $employeeId,
        dormitoryId: $dormitoryId,
        type: RequestType::Personal,
        checkInDate: new DateTimeImmutable('2026-07-01', new DateTimeZone('UTC')),
        checkOutDate: new DateTimeImmutable('2026-07-31', new DateTimeZone('UTC')),
    ));

    $secondCode = $generator->generate($at);

    expect($firstCode->sequence())->toBe(1);
    expect($secondCode->sequence())->toBe(2);
    expect($secondCode->datePart())->toBe('20260623');
});
