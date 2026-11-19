<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Domain\ValueObject;

use App\Catalog\Domain\ValueObject\Balance;
use PHPUnit\Framework\TestCase;

final class BalanceTest extends TestCase
{
    public function testBalanceAllowsZeroUnits(): void
    {
        self::assertSame(0, (new Balance(0))->getUnits());
    }

    public function testBalanceRejectsNegativeUnits(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Balance(-1);
    }
}
