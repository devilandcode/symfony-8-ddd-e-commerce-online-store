<?php

declare(strict_types=1);

namespace App\Tests\Storefront;

use App\Storefront\Application\CatalogPage\Input\PageNumber;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class PageNumberTest extends TestCase
{
    public function testItDefaultsToTheFirstPage(): void
    {
        self::assertSame(1, (new PageNumber())->value);
    }

    #[TestWith([0])]
    #[TestWith([-3])]
    public function testItRejectsNonPositivePages(int $value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new PageNumber($value);
    }
}
