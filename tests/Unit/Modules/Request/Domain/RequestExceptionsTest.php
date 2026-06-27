<?php

declare(strict_types=1);

use App\Modules\Request\Domain\Exceptions\InvalidGroupRequestException;
use App\Modules\Request\Domain\Exceptions\InvalidRequestTransitionException;
use App\Modules\Request\Domain\Exceptions\RequestDomainException;
use App\Modules\Request\Domain\Exceptions\RequestNotEligibleException;
use App\Modules\Request\Domain\Exceptions\RequestNotFoundException;
use App\Modules\Request\Domain\Exceptions\RequestValidationException;

it('requires all request domain exceptions to extend RequestDomainException', function (string $exception): void {
    expect(is_subclass_of($exception, RequestDomainException::class))->toBeTrue();
})->with([
    RequestNotFoundException::class,
    InvalidRequestTransitionException::class,
    RequestNotEligibleException::class,
    RequestValidationException::class,
    InvalidGroupRequestException::class,
]);

it('defines RequestDomainException as the abstract module base', function (): void {
    expect((new ReflectionClass(RequestDomainException::class))->isAbstract())->toBeTrue();
});

it('carries eligibility reason codes on request not eligible exception', function (): void {
    $exception = new RequestNotEligibleException(
        reasonCodes: ['employee_inactive', 'pending_request_exists'],
    );

    expect($exception->reasonCodes)->toBe(['employee_inactive', 'pending_request_exists']);
});

it('supports aggregate invariant messages on typed request exceptions', function (): void {
    expect((new RequestValidationException('Check-out date must be after check-in date.'))->getMessage())
        ->toBe('Check-out date must be after check-in date.');
    expect((new InvalidRequestTransitionException('Cannot cancel a request in a pending approval stage.'))->getMessage())
        ->toBe('Cannot cancel a request in a pending approval stage.');
    expect((new InvalidGroupRequestException('Mission request must have between 2 and 20 members.'))->getMessage())
        ->toBe('Mission request must have between 2 and 20 members.');
});
