<?php

declare(strict_types=1);

namespace App\Tests\Storefront;

use App\Storefront\Application\CatalogPage\Input\CatalogPageFilters;
use PHPUnit\Framework\TestCase;

final class CatalogPageFiltersTest extends TestCase
{
    public function testItRejectsNonPositiveIds(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new CatalogPageFilters(brandIds: [0]);
    }

    public function testItRejectsEmptyCharacteristicValues(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new CatalogPageFilters(characteristicValues: [4 => ['']]);
    }

    public function testItKeepsValidFilters(): void
    {
        $filters = new CatalogPageFilters([3], [9], [11 => ['Spray']]);

        self::assertSame([3], $filters->categoryIds);
        self::assertSame([9], $filters->brandIds);
        self::assertSame([11 => ['Spray']], $filters->characteristicValues);
    }
}
