<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Application;

use App\Catalog\Application\CatalogBrowse\Input\CatalogBrowseCriteria;
use PHPUnit\Framework\TestCase;

final class CatalogBrowseCriteriaTest extends TestCase
{
    public function testItRejectsNonPositiveIds(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new CatalogBrowseCriteria(brandIds: [0]);
    }

    public function testItRejectsEmptyCharacteristicValues(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new CatalogBrowseCriteria(characteristicValues: [4 => ['']]);
    }

    public function testItKeepsACategorySlugTogetherWithCharacteristicValues(): void
    {
        $criteria = new CatalogBrowseCriteria(
            categorySlug: 'face',
            brandIds: [9],
            characteristicValues: [11 => ['Spray']],
        );

        self::assertSame('face', $criteria->categorySlug);
        self::assertSame([], $criteria->categoryIds);
        self::assertSame([9], $criteria->brandIds);
        self::assertSame([11 => ['Spray']], $criteria->characteristicValues);
    }
}
