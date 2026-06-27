<?php

declare(strict_types=1);

use App\Modules\Request\Domain\ValueObjects\RequestCode;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use App\Support\Exceptions\ValidationException;
use Ramsey\Uuid\Uuid;

it('accepts a valid request identifier', function (): void {
    $uuid = Uuid::uuid7()->toString();
    $id = RequestId::fromString($uuid);

    expect((string) $id)->toBe($uuid);
    expect($id->equals(RequestId::fromString($uuid)))->toBeTrue();
});

it('rejects an invalid request identifier', function (): void {
    expect(fn () => RequestId::fromString('not-a-uuid'))
        ->toThrow(ValidationException::class);
});

it('accepts a valid request code', function (): void {
    $code = RequestCode::fromString('REQ-20260623-0001');

    expect((string) $code)->toBe('REQ-20260623-0001');
    expect($code->datePart())->toBe('20260623');
    expect($code->sequence())->toBe(1);
});

it('rejects invalid request code formats', function (string $invalid): void {
    expect(RequestCode::isValid($invalid))->toBeFalse();
    expect(fn () => RequestCode::fromString($invalid))
        ->toThrow(ValidationException::class);
})->with([
    'wrong prefix' => ['ABC-20260623-0001'],
    'bad date' => ['REQ-20260230-0001'],
    'zero sequence' => ['REQ-20260623-0000'],
    'overflow sequence' => ['REQ-20260623-10000'],
    'short sequence' => ['REQ-20260623-001'],
]);
