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
        expect(new LotteryProgramNotFoundException('missing'))->toBeInstanceOf(LotteryProgramNotFoundException::class);
        expect(new InvalidLotteryTransitionException('invalid'))->toBeInstanceOf(InvalidLotteryTransitionException::class);
        expect(new RegistrationClosedException('closed'))->toBeInstanceOf(RegistrationClosedException::class);
        expect(new DrawNotAllowedException('not allowed'))->toBeInstanceOf(DrawNotAllowedException::class);
        expect(new DuplicateEnrollmentException('duplicate'))->toBeInstanceOf(DuplicateEnrollmentException::class);
    }
}
