<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Domain\ValueObject;

use App\Catalog\Domain\ValueObject\OldPrice;
use PHPUnit\Framework\TestCase;

final class OldPriceTest extends TestCase
{
    public function testOldPriceRequiresPositiveMinorUnits(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new OldPrice(0);
    }
}
