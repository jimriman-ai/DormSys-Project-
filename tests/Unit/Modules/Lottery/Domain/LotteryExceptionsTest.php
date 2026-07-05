<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Lottery\Domain;

use App\Modules\Lottery\Domain\Exceptions\DrawNotAllowedException;
use App\Modules\Lottery\Domain\Exceptions\DuplicateEnrollmentException;
use App\Modules\Lottery\Domain\Exceptions\InvalidLotteryTransitionException;
use App\Modules\Lottery\Domain\Exceptions\LotteryProgramNotFoundException;
use App\Modules\Lottery\Domain\Exceptions\RegistrationClosedException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LotteryExceptionsTest extends TestCase
{
    #[Test]
    public function it_instantiates_lottery_domain_exceptions(): void
    {
        $notFound = new LotteryProgramNotFoundException('missing');
        $invalidTransition = new InvalidLotteryTransitionException('invalid');
        $registrationClosed = new RegistrationClosedException('closed');
        $drawNotAllowed = new DrawNotAllowedException('not allowed');
        $duplicateEnrollment = new DuplicateEnrollmentException('duplicate');

        expect($notFound->getMessage())->toBe('missing');
        expect($invalidTransition->getMessage())->toBe('invalid');
        expect($registrationClosed->getMessage())->toBe('closed');
        expect($drawNotAllowed->getMessage())->toBe('not allowed');
        expect($duplicateEnrollment->getMessage())->toBe('duplicate');
    }
}
